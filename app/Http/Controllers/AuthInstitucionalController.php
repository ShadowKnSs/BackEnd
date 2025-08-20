<?php
// app/Http/Controllers/InstitucionalAuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use SimpleXMLElement;
use App\Models\Usuario;
use App\Models\TipoUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthInstitucionalController extends Controller
{
    private $serviceUrl;
    private $serviceLoginFormUrl;
    private $serviceClientId;
    private $serviceReturnURL;

    public function __construct()
    {
        $this->serviceUrl = env('UASLP_SERVICE_URL', 'https://login.uaslp.mx/LoginService/LoginService.svc?wsdl');
        $this->serviceLoginFormUrl = env('UASLP_LOGIN_FORM_URL', 'https://login.uaslp.mx/LoginGateway/Default.aspx');
        $this->serviceClientId = env('UASLP_CLIENT_ID', 4);
        $this->serviceReturnURL = env('UASLP_RETURN_URL', url('/api/login-institucional/callback'));
    }

    public function redirectToInstitucionalLogin()
    {
        try {
            Log::debug('Iniciando redirección a login institucional');
            
            $soapClient = new SoapClient($this->serviceUrl, ['soap_version' => SOAP_1_1]);
            
            $params = ['idAplicacion' => $this->serviceClientId, 'ReturnUrl' => $this->serviceReturnURL];
            Log::debug('Parámetros para NuevaSesionConUrlRetorno:', $params);
            
            $response = $soapClient->NuevaSesionConUrlRetorno($params);
            
            $xml = new SimpleXMLElement($response->NuevaSesionConUrlRetornoResult);
            $ticket = (int)$xml['Ticket'];
            $sessionKey = (string)$xml['ClaveSesion'];
            
            Log::debug('Ticket obtenido: ' . $ticket);
            Log::debug('Clave de sesión obtenida: ' . $sessionKey);
            
            // Guardar en cookie por 10 minutos
            setcookie("SC", $sessionKey, time() + 600, "/", "", false, true);
            
            Log::debug('Redirigiendo a formulario de login institucional');
            return redirect($this->serviceLoginFormUrl . '?Ticket=' . $ticket);
        } catch (\Exception $e) {
            Log::error('Error en redirectToInstitucionalLogin: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al conectar con el servicio de autenticación',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
{
    Log::debug('Callback recibido del login institucional');
    Log::debug('Parámetros recibidos: ', $request->all());
    Log::debug('Cookie SC: ' . ($_COOKIE['SC'] ?? 'No encontrada'));
    
    if (!$request->has('Ticket') || !isset($_COOKIE['SC'])) {
        Log::error('Faltan parámetros necesarios: Ticket o cookie SC');
        return response()->json(['error' => 'Solicitud inválida'], 400);
    }
    
    $ticket = $request->get('Ticket');
    $sessionKey = $_COOKIE['SC'];
    
    Log::debug('Ticket: ' . $ticket);
    Log::debug('Clave de sesión: ' . $sessionKey);
    
    // Limpiar cookie
    setcookie("SC", "", time() - 3600, "/");
    
    try {
        $soapClient = new SoapClient($this->serviceUrl, ['soap_version' => SOAP_1_1]);
        $params = ['Ticket' => $ticket, 'ClaveSesion' => $sessionKey];
        
        Log::debug('Validando ticket con parámetros: ', $params);
        
        // Validar ticket
        $validationResponse = $soapClient->ValidaCliente($params);
        $isValid = $validationResponse->ValidaClienteResult;
        
        Log::debug('Resultado de validación: ' . $isValid);
        
        if ($isValid != 1) {
            Log::error('Validación de ticket fallida: ' . $isValid);
            return response()->json(['error' => 'Autenticación fallida'], 401);
        }
        
        // Obtener información del usuario
        $userResponse = $soapClient->EstadoUsuario($params);
        $xml = new SimpleXMLElement($userResponse->EstadoUsuarioResult);
        
        Log::debug('Respuesta XML del servicio:');
        Log::debug($xml->asXML());
        
        // Extraer atributos correctamente (los datos están como atributos, no como elementos)
        $autenticado = (bool)$xml['Autenticado'];
        $textoError = isset($xml['TextoError']) ? (string)$xml['TextoError'] : null;
        
        if (!$textoError && $autenticado) {
            // Extraer RPE y otros datos de los atributos
            $rpe = (string)$xml['Usuario'];
            $userType = (string)$xml['Tipo'];
            $nombre = (string)$xml['Nombre'];
            
            Log::debug('RPE obtenido del servicio (atributo): ' . $rpe);
            Log::debug('Tipo de usuario (atributo): ' . $userType);
            Log::debug('Nombre (atributo): ' . $nombre);
            
            // Si el RPE está vacío pero tenemos el atributo Clave, intentar extraerlo de allí
            if (empty($rpe) && isset($xml['Clave'])) {
                $clave = (string)$xml['Clave'];
                Log::debug('Clave obtenida: ' . $clave);
                
                // Intentar extraer RPE de la clave (ej: "A338885" -> "338885")
                if (preg_match('/[aA]?(\d+)/', $clave, $matches)) {
                    $rpe = $matches[1];
                    Log::debug('RPE extraído de Clave: ' . $rpe);
                }
            }
            
            // Buscar usuario en base de datos por RPE
            Log::debug('Buscando usuario con RPE: ' . $rpe);
            $user = Usuario::where('RPE', $rpe)->first();
            
            if (!$user) {
                    Log::warning('Usuario no encontrado con RPE: ' . $rpe);
                    // Redirigir al frontend con error
                    $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
                    $redirectUrl = $frontendUrl . '/login?error=Usuario no registrado en el sistema&rpe=' . $rpe;
                    return redirect($redirectUrl);
                }

                Log::debug('Usuario encontrado: ', ['id' => $user->idUsuario, 'nombre' => $user->nombre]);

                // Obtener roles y permisos del usuario
                $roles = DB::table('usuario_tipo as ut')
                    ->join('tipoUsuario as t', 'ut.idTipoUsuario', '=', 't.idTipoUsuario')
                    ->where('ut.idUsuario', $user->idUsuario)
                    ->select('t.idTipoUsuario', 't.nombreRol')
                    ->get();

                foreach ($roles as $rol) {
                    $rol->permisos = DB::table('permiso')
                        ->where('idTipoUser', $rol->idTipoUsuario)
                        ->select('modulo', 'tipoAcceso')
                        ->get();
                }

                // Generar token de autenticación
                $token = $user->createToken('InstitucionalAuth')->plainTextToken;

                Log::debug('Token generado: ' . $token);
                Log::debug('Autenticación exitosa para usuario: ' . $user->RPE);

                // Redirigir al frontend con los datos en la URL
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
                $redirectUrl = $frontendUrl . '/login?' . http_build_query([
                    'institucional' => 'true',
                    'token' => $token,
                    'usuario' => json_encode($user),
                    'roles' => json_encode($roles),
                    'success' => 'true'
                ]);

                return redirect($redirectUrl);
        } else {
            $errorText = $textoError ?: 'Desconocido';
            Log::error('Error en autenticación: ' . $errorText);
            return response()->json(['error' => 'Error en autenticación: ' . $errorText], 401);
        }
    } catch (\Exception $e) {
        Log::error('Excepción en handleCallback: ' . $e->getMessage());
        Log::error('Trace: ' . $e->getTraceAsString());
        return response()->json([
            'error' => 'Error en el servicio de autenticación',
            'message' => $e->getMessage()
        ], 500);
    }
}
}