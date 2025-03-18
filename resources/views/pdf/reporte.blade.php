<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Semestral</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .conclusion { margin-top: 20px; font-size: 16px; }
        .image-container img { width: 100%; max-width: 700px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Reporte Semestral</h1>

    <div class="image-container">
        @if($imageBase64)
            <img src="data:image/png;base64,{{ $imageBase64 }}" alt="Reporte">
        @endif
    </div>

    <div class="conclusion">
        <h2>Conclusión</h2>
        <p>{{ $conclusion }}</p>
    </div>
</body>
</html>
