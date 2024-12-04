<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 정답 설정
    $answers = [
        "question1" => ["Dual Stack", "듀얼스택", "DualStack", "듀얼 스택"],
        "question2" => "NAT",
        "question3" => "RIP"
    ];

    // 클라이언트에서 전송된 데이터 받기
    $data = json_decode(file_get_contents('php://input'), true);

    // 대소문자와 공백을 무시하며 비교
    $isCorrect = true;
    foreach ($answers as $key => $correctAnswer) {
        if (is_array($correctAnswer)) {
            $isValid = false;
            foreach ($correctAnswer as $validAnswer) {
                if (strcasecmp(preg_replace('/\s+/', '', trim($data[$key])), preg_replace('/\s+/', '', $validAnswer)) === 0) {
                    $isValid = true;
                    break;
                }
            }
            if (!$isValid) {
                $isCorrect = false;
                break;
            }
        } else {
            if (!isset($data[$key]) || strcasecmp(preg_replace('/\s+/', '', trim($data[$key])), preg_replace('/\s+/', '', $correctAnswer)) !== 0) {
                $isCorrect = false;
                break;
            }
        }
    }

    // 결과 반환
    if ($isCorrect) {
        echo "모든 문제를 정확히 해결했습니다!\n플래그: FLAG{network_admin_pro}"; // Return plain text
    } else {
        echo "틀린 답이 있습니다. 모든 문제를 맞춰야 플래그가 생성됩니다."; // Return plain text
    }
    exit; // Stop further execution after handling the POST request
}

// HTML content starts here
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge 36: Network Admin Pro</title>
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
            max-width: 800px;
            background-color: #1e1e1e;
            border: 1px solid #00ff00;
            border-radius: 5px;
        }

        input {
            background-color: #2a2a2a;
            color: #00ff00;
            border: 1px solid #00ff00;
            padding: 10px;
            width: 97%;
            margin: 10px auto;
            border-radius: 3px;
            text-align: center;
        }

        button {
            background-color: #00ff00;
            color: #000;
            border: 1px solid #00ff00;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }

        button:hover {
            background-color: #33ff99;
        }

        #result, #flag {
            margin-top: 20px;
            font-size: 1.2em;
        }

        .question {
            margin-bottom: 30px;
            text-align: left;
            background-color: #2a2a2a;
            padding: 20px;
            border: 1px solid #00ff00;
            border-radius: 5px;
        }

        .question p {
            margin: 10px 0;
        }

        .question b {
            color: #00ff00;
        }
    </style>
</head>
<body>
    <h1>Network Admin Pro</h1>
    <div class="container">
        <div class="question">
            <p><b>1. 네트워크에서 IPv4와 IPv6를 동시에 사용하는 기술을 말합니다. 이 기술은 두 프로토콜이 서로 다른 네트워크 환경에서 동시에 동작할 수 있도록 하여, IPv4에서 IPv6로의 원활한 전환을 지원합니다.</b></p>
            <input type="text" id="question1" placeholder="기술 이름 입력">
        </div>

        <div class="question">
            <p><b>2. 내부 네트워크의 여러 IP 주소를 하나의 공인 IP 주소로 변환하여 인터넷과 연결하는 기술입니다. 이를 통해 내부 네트워크는 외부에서 접근할 수 없으며, 여러 장치들이 하나의 공인 IP 주소를 공유하면서 인터넷에 연결할 수 있습니다.</b></p>
            <input type="text" id="question2" placeholder="기술 이름 입력">
        </div>

        <div class="question">
            <p><b>3. 거리 벡터 프로토콜로 작동하며, 라우팅 정보는 주기적으로 교환됩니다. 최대 홉 수는 15로 제한되며, 16은 도달할 수 없는 네트워크로 간주됩니다.</b></p>
            <input type="text" id="question3" placeholder="프로토콜 이름 입력">
        </div>

        <button onclick="submitAnswers()">제출</button>
        <p id="result"></p>
        <p id="flag"></p>
    </div>

    <script>
        async function submitAnswers() {
            const answers = {
                question1: document.getElementById('question1').value.trim(),
                question2: document.getElementById('question2').value.trim(),
                question3: document.getElementById('question3').value.trim(),
            };

            const response = await fetch("index.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(answers),
            });

            const resultText = await response.text(); // Change to text response

            const resultElement = document.getElementById('result');
            const flagElement = document.getElementById('flag');

            if (resultText.includes("모든 문제를 정확히 해결했습니다!")) {
                resultElement.style.color = '#00ff00';
                resultElement.textContent = resultText; // Display success message
                flagElement.style.color = '#00ff00';
                flagElement.textContent = ''; // Clear flag since it's now in result
            } else {
                resultElement.style.color = '#ff0000';
                resultElement.textContent = resultText; // Display error message
                flagElement.textContent = '';
            }
        }
    </script>
</body>
</html>