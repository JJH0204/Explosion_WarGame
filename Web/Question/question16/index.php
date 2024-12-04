<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    $userInput = $data['query'] ?? '';

    // 세미콜론 체크
    if (substr($userInput, -1) !== ';') {
        echo json_encode([
            "status" => "error", 
            "message" => "SQL 구문이 올바르지 않습니다."
        ]);
        exit;
    }

    // userDB.test의 모든 가능한 형식들
    $validDBFormats = [
        'userDB.test',
        '`userDB`.test',
        'userDB.`test`',
        '`userDB`.`test`'
    ];

    // userDB.test 형식 체크
    $dbValid = false;
    foreach ($validDBFormats as $format) {
        if (strpos($userInput, $format) !== false) {
            $dbValid = true;
            break;
        }
    }

    if (!$dbValid) {
        echo json_encode([
            "status" => "error", 
            "message" => "테이블명이 올바르지 않습니다."
        ]);
        exit;
    }

    // test@localhost의 모든 가능한 형식들
    $validHostFormats = [ 
        'test@localhost',
        'test@\'localhost\'',
        '\'test\'@localhost',
        'test@`localhost`',
        '`test`@localhost',
        '\'test\'@\'localhost\'',
        '`test`@`localhost`',
        '\'test\'@`localhost`',
        '`test`@\'localhost\'',
    ];

    // test@localhost 형식 체크
    $hostValid = false;
    foreach ($validHostFormats as $format) {
        if (strpos($userInput, $format) !== false) {
            $hostValid = true;
            break;
        }
    }

    if (!$hostValid) {
        echo json_encode([
            "status" => "error", 
            "message" => "사용자 지정이 올바르지 않습니다."
        ]);
        exit;
    }

    // 기본 구문 구조 체크 (GRANT, SELECT/DELETE, ON, TO 순서와 공백)
    $pattern = '/^grant\s+(select\s*,\s*delete|delete\s*,\s*select)\s+on\s+.*\s+to\s+/i';
    
    if (preg_match($pattern, $userInput)) {
        echo json_encode([
            "status" => "success", 
            "flag" => "FLAG{sql_grant_correct}"
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "SQL 구문이 올바르지 않습니다."
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
    <title>Challenge 16: SQL Grant</title>
    <style>
        body {
            background-color: #121212;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            text-align: center;
            padding: 20px;
            margin: 0;
            min-height: 100vh;
        }

        .container {
            margin: 40px auto 0;
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
            width: 80%;
            height: 100px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            border-radius: 5px;
            resize: vertical;
        }

        button {
            background-color: #00ff00;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            border-radius: 5px;
        }

        button:hover {
            background-color: #33ff99;
        }

        #result {
            margin-top: 20px;
            font-size: 1.2em;
            padding: 15px;
            background-color: #2a2a2a;
            border-radius: 5px;
            border: 1px solid #00ff00;
        }
    </style>
</head>
<body>
    <h1>SQL Grant</h1>
    <div class="container">
        <p>'test'@'localhost' 유저에게 userDB.test 테이블에 SELECT, DELETE 권한을 부여하는 SQL 명령어를 입력하세요.</p>
        <textarea id="sqlQuery" placeholder="SQL 명령어 입력"></textarea>
        <button onclick="submitQuery()">명령어 실행</button>
        <p id="result"></p>
    </div>

    <script>
        async function submitQuery() {
            const query = document.getElementById('sqlQuery').value;
            const result = document.getElementById('result');

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    result.style.color = '#00ff00';
                    result.textContent = `축하합니다! 플래그: ${data.flag}`;
                } else {
                    result.style.color = '#ff0000';
                    result.textContent = data.message;
                }
            } catch (error) {
                result.style.color = '#ff0000';
                result.textContent = '오류 발생: 요청을 처리할 수 없습니다.';
            }
        }
    </script>
</body>
</html>
