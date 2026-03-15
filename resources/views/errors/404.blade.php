<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan | Panti Asuhan Kasih Agape</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        :root {
            --main-color: #0077b6;
            --heading-color: #023e8a;
            --text-color: #495057;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f7ff 0%, #e0efff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            padding: 5rem 3rem;
            border-radius: 3rem;
            box-shadow: 0 20px 50px rgba(0, 119, 182, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            max-width: 500px;
            width: 90%;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-code {
            font-size: 10rem;
            font-weight: 700;
            color: var(--main-color);
            margin: 0;
            line-height: 1;
            text-shadow: 4px 4px 0 rgba(0, 119, 182, 0.1);
        }

        h1 {
            font-size: 2.4rem;
            color: var(--heading-color);
            margin: 1rem 0;
        }

        p {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 2.5rem;
        }

        .btn-home {
            display: inline-block;
            background: var(--main-color);
            color: #fff;
            padding: 1.2rem 3rem;
            border-radius: 5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 119, 182, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 119, 182, 0.4);
            background: #0096c7;
        }

        .back-icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Ups! Halaman Hilang</h1>
        <p>Maaf, halaman yang Anda cari tidak dapat ditemukan atau telah dipindahkan.</p>
        <a href="/" class="btn-home">
            <i class="fas fa-arrow-left back-icon"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>
