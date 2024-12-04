<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 정답 플래그 설정
$correct_flag = "shadows";
$final_flag = "Flag{hashing_practice}";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_flag = isset($_POST['flag']) ? $_POST['flag'] : '';

    // 제출된 플래그 확인
    if ($submitted_flag === $correct_flag) {
        echo json_encode([
            'success' => true,
            'flag' => $final_flag
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '틀린 플래그입니다.'
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge 22: Cryptanalysis 2</title>
    <style>
        body {
            background-color: #121212;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            text-align: center;
            padding: 20px;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
            max-width: 600px;
            background-color: #1e1e1e;
            border: 1px solid #00ff00;
            border-radius: 5px;
        }

        textarea {
            background-color: #2a2a2a;
            color: #00ff00;
            border: 1px solid #00ff00;
            padding: 10px;
            width: 60%;
            height: 20px;
            margin: 10px auto;
            border-radius: 5px;
            resize: none;
            display: block;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow: hidden;
        }

        input {
            display: block;
            width: 30%;
            margin: 10px auto;
            padding: 10px;
            text-align: center;
            background-color: #2a2a2a;
            color: #00ff00;
            border: 1px solid #00ff00;
            border-radius: 5px;
        }

        button {
            background-color: #00ff00;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px;
        }

        button:hover {
            background-color: #33ff99;
        }

        #result {
            margin-top: 20px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <h1>Cryptanalysis 2</h1>
    <div class="container">
        <p>암호화된 메시지를 해독하여 올바른 플래그를 제출하세요.</p>
        <textarea id="cipherText" readonly style="width: 50%; text-align: center;">8f6b1ecc7d7c0377c707ec6913cbbc3d</textarea>
        <p>해독된 플래그를 입력하세요:</p>
        <input type="text" id="userInput" placeholder="해독된 플래그 입력" style="display: block; width: 30%; margin: 10px auto; padding: 10px; text-align: center; background-color: #2a2a2a; color: #00ff00; border: 1px solid #00ff00; outline: none;">
        <div class="button-group">
            <button onclick="checkFlag()">제출</button>
        </div>
        <p id="result"></p>
    </div>

    <script>
        async function checkFlag() {
            const userFlag = document.getElementById('userInput').value.trim();
            const resultElement = document.getElementById('result');

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'flag=' + encodeURIComponent(userFlag)
                });

                const data = await response.json();

                if (data.success) {
                    resultElement.style.color = '#00ff7f';
                    resultElement.textContent = `정답입니다! 플래그: ${data.flag}`;
                } else {
                    resultElement.style.color = '#ff4d4d';
                    resultElement.textContent = '틀렸습니다. 다시 시도하세요.';
                }
            } catch (error) {
                resultElement.style.color = '#ff4d4d';
                resultElement.textContent = '오류가 발생했습니다. 다시 시도해주세요.';
            }
        }

    function toggleSSHInfo() {
        const sshInfo = document.getElementById('sshInfo');
        if (sshInfo.style.display === 'none') {
            sshInfo.style.display = 'block';
        } else {
            sshInfo.style.display = 'none';
        }
    }

    </script>
</body>
</html>
