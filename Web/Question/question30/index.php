<!DOCTYPE html>
<html>
<head>
    <title>Challenge 30: 2D JumpKing</title>
    <style>
        canvas {
            border: 2px solid black;
            background: #f0f0f0;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <canvas id="gameCanvas" width="800" height="400"></canvas>

<script>
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');

// 상수 정의
const WORLD_HEIGHT = 10000;
const MINIMAP_WIDTH = 100;
const MINIMAP_HEIGHT = 200;
let showMinimap = false;

// player 초기 위치 수정
const player = {
    x: 50,
    y: WORLD_HEIGHT - 100,  // 맵 바닥 근처에서 시작
    width: 40,
    height: 40,
    velocityX: 0,
    velocityY: 0,
    speed: 5,
    jumpForce: 0,
    maxJumpForce: 15,
    chargeRate: 0.5,
    gravity: 0.5,
    isJumping: false,
    isCharging: false,
    direction: 1,
    image: new Image(),
    bounceForce: 1.2,    // 벽에서 튕기는 힘
    friction: 0.8        // 마찰력 (튕김 감소)
};
player.image.src = './images/pepe.png';

// 배경 이미지 추가
const backgroundImage = new Image();
backgroundImage.src = './images/snowy_background.png';  // 눈 내리는 배경 이미지

// 플랫폼 위치 수정
const platforms = [
    { x: 500, y: WORLD_HEIGHT - 50, width: 200, height: 20, type: 'start' },     // 시작 지점
    { x: 200, y: WORLD_HEIGHT - 200, width: 100, height: 20, type: 'clue' },    // 첫 번째 단서
    { x: 50, y: WORLD_HEIGHT - 350, width: 100, height: 20, type: 'clue' },     // 두 번째 단서
    { x: 250, y: WORLD_HEIGHT - 500, width: 100, height: 20, type: 'clue' },    // 세 번째 단서
    { x: 100, y: WORLD_HEIGHT - 650, width: 100, height: 20, type: 'photo' },   // 사진 조각
    { x: 400, y: WORLD_HEIGHT - 800, width: 100, height: 20, type: 'museum' },   // 박물관 입구
    // 추가 발판들
    { x: 600, y: WORLD_HEIGHT - 950, width: 100, height: 20, type: 'normal' },
    { x: 600, y: WORLD_HEIGHT - 1100, width: 100, height: 20, type: 'normal' },
    { x: 400, y: WORLD_HEIGHT - 1250, width: 100, height: 20, type: 'normal' },
    { x: 100, y: WORLD_HEIGHT - 1400, width: 100, height: 20, type: 'normal' },
    { x: 350, y: WORLD_HEIGHT - 1550, width: 100, height: 20, type: 'normal' },
    { x: 200, y: WORLD_HEIGHT - 1700, width: 100, height: 20, type: 'normal' },
    { x: 450, y: WORLD_HEIGHT - 1850, width: 100, height: 20, type: 'normal' },
    { x: 150, y: WORLD_HEIGHT - 2000, width: 100, height: 20, type: 'normal' },
    { x: 300, y: WORLD_HEIGHT - 2150, width: 100, height: 20, type: 'normal' },
    { x: 400, y: WORLD_HEIGHT - 2300, width: 100, height: 20, type: 'normal' },
    // ... 계속해서 50개까지
    { x: 250, y: WORLD_HEIGHT - 3200, width: 100, height: 20, type: 'normal' },
    { x: 400, y: WORLD_HEIGHT - 3350, width: 100, height: 20, type: 'normal' },
    { x: 150, y: WORLD_HEIGHT - 3500, width: 100, height: 20, type: 'normal' },
    { x: 300, y: WORLD_HEIGHT - 3650, width: 100, height: 20, type: 'normal' },
    { x: 200, y: WORLD_HEIGHT - 3800, width: 100, height: 20, type: 'normal' },
    { x: 200, y: WORLD_HEIGHT - 4800, width: 100, height: 20, type: 'normal' },
    { x: 450, y: WORLD_HEIGHT - 4950, width: 100, height: 20, type: 'normal' },
    { x: 150, y: WORLD_HEIGHT - 5100, width: 100, height: 20, type: 'normal' },
    { x: 300, y: WORLD_HEIGHT - 5250, width: 100, height: 20, type: 'normal' },
    { x: 400, y: WORLD_HEIGHT - 5400, width: 100, height: 20, type: 'normal' },
    { x: 250, y: WORLD_HEIGHT - 5550, width: 100, height: 20, type: 'normal' },
    { x: 400, y: WORLD_HEIGHT - 5700, width: 100, height: 20, type: 'normal' },
    { x: 150, y: WORLD_HEIGHT - 5850, width: 100, height: 20, type: 'normal' },
    { x: 300, y: WORLD_HEIGHT - 6000, width: 100, height: 20, type: 'normal' },
    { x: 200, y: WORLD_HEIGHT - 6150, width: 100, height: 20, type: 'normal' },
    // 마지막 발판들
    { x: 350, y: WORLD_HEIGHT - 6300, width: 100, height: 20, type: 'normal' },
    { x: 250, y: WORLD_HEIGHT - 6450, width: 100, height: 20, type: 'normal' },
    { x: 400, y: WORLD_HEIGHT - 6600, width: 100, height: 20, type: 'normal' }
];

// 단서 텍스트 추가
const clues = {
    'clue1': "희미한 글씨: '오래된 시계탑 근처...'",
    'clue2': "찢어진 사진 조각: '눈 내리는 거리의...'",
    'clue3': "흐릿한 메모: '붉은 벽돌 건물...'"
};

let keys = {
    right: false,
    left: false,
    space: false
};

let isCommandMode = false;
let commandInput = '';

// 커맨드 입력 UI 생성
const commandUI = document.createElement('div');
commandUI.style.cssText = `
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    padding: 20px;
    border-radius: 5px;
    color: white;
    display: none;
    z-index: 1000;
`;
commandUI.innerHTML = `
    <input type="text" id="commandInput" style="
        background: black;
        color: lime;
        border: 1px solid lime;
        padding: 5px;
        outline: none;
    ">
`;
document.body.appendChild(commandUI);

// commandInput 이벤트 리스너 추가
document.getElementById('commandInput').addEventListener('keyup', (e) => {
    if (e.key === 'Enter') {
        const command = e.target.value.toLowerCase();
        if (command === 'pepe') {
            // 버튼 생성
            const button = document.createElement('button');
            button.textContent = 'Go to Question 30';
            button.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 10px;
                background: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                z-index: 1000;
            `;
            button.onclick = () => {
                window.location.href = 'hidden.html';
            };
            document.body.appendChild(button);
        }
        // 커맨드 입력 후 UI 숨기기
        commandUI.style.display = 'none';
        isCommandMode = false;
    } else if (e.key === 'Escape') {
        // ESC 키로 커맨드 모드 취소
        commandUI.style.display = 'none';
        isCommandMode = false;
    }
});

document.addEventListener('keydown', (e) => {
    if (!player.isJumping && !player.isCharging) {
        if (e.key === 'ArrowRight') {
            keys.right = true;
            player.direction = 1;
        }
        if (e.key === 'ArrowLeft') {
            keys.left = true;
            player.direction = -1;
        }
    }
    if (e.key === ' ' && !player.isJumping && !keys.space) {
        keys.space = true;
        player.isCharging = true;
        player.jumpForce = 0;
    }
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'q') {
        if (isCommandMode) {
            // 커맨드 모드가 활성화된 상태에서는 닫기
            isCommandMode = false;
            commandUI.style.display = 'none';
        } else {
            // 커맨드 모드가 비활성화된 상태에서는 열기
            isCommandMode = true;
            commandUI.style.display = 'block';
            const commandInput = document.getElementById('commandInput');
            commandInput.value = '';
            commandInput.focus();
        }
        e.preventDefault();
    }
    if (e.key.toLowerCase() === 'm') {
        showMinimap = !showMinimap;
        e.preventDefault();
    }
});

document.addEventListener('keyup', (e) => {
    if (e.key === 'ArrowRight') keys.right = false;
    if (e.key === 'ArrowLeft') keys.left = false;
    if (e.key === ' ' && player.isCharging) {
        keys.space = false;
        player.isCharging = false;
        player.isJumping = true;
        player.velocityY = -player.jumpForce;
        player.velocityX = player.direction * (player.jumpForce * 0.5);
    }
});

// 충돌 감지 함수 수정
function checkCollision(player, platform) {
    const playerBottom = player.y + player.height;
    const playerTop = player.y;
    const playerLeft = player.x;
    const playerRight = player.x + player.width;

    const platformBottom = platform.y + platform.height;
    const platformTop = platform.y;
    const platformLeft = platform.x;
    const platformRight = platform.x + platform.width;

    // 이전 프레임의 위치를 저장하는 변수 추가
    const prevPlayerBottom = player.prevY + player.height;

    if (playerRight > platformLeft && 
        playerLeft < platformRight && 
        playerBottom > platformTop && 
        playerTop < platformBottom) {
        
        // 이전 프레임에서 플랫폼 위에 있었는지 확인
        if (prevPlayerBottom <= platformTop) {
            // 위에서 아래로 충돌한 경우
            player.y = platformTop - player.height;
            player.velocityY = 0;
            player.isJumping = false;
        } else {
            // 다른 방향에서의 충돌 (측면 또는 아래에서 위로)
            // 충돌 해결: 플레이어를 밀어냄
            if (playerBottom - platformTop < 20) { // 위쪽 충돌
                player.y = platformTop - player.height;
                player.velocityY = 0;
            } else if (platformBottom - playerTop < 20) { // 아래쪽 충돌
                player.y = platformBottom;
                player.velocityY = 0;
            } else if (playerRight - platformLeft < 20) { // 왼쪽 충돌
                player.x = platformLeft - player.width;
            } else if (platformRight - playerLeft < 20) { // 오른쪽 충돌
                player.x = platformRight;
            }
        }

        // 마지막 발판에 착지했을 때
        if (platform.type === 'normal' && platform.y === WORLD_HEIGHT - 6600) {
            alert('돌아가!');
            resetToStart();
        }

        return true;
    }
    return false;
}

// 카메라 객체
const camera = {
    y: WORLD_HEIGHT - canvas.height  // 처음에 바닥이 보이도록 설정
};

function update() {
    if (isCommandMode) return;  // 커맨드 모드일 때는 게임 업데이트 중지
    
    if (player.isCharging && player.jumpForce < player.maxJumpForce) {
        player.jumpForce += player.chargeRate;
    }

    player.velocityY += player.gravity;
    player.y += player.velocityY;

    if (player.y + player.height > WORLD_HEIGHT) {
        player.y = WORLD_HEIGHT - player.height;
        player.velocityY = 0;
        player.isJumping = false;
        player.velocityX *= player.friction; // 착지 시 수평 속도 감소
    }

    if (!player.isJumping && !player.isCharging) {
        if (keys.right) player.x += player.speed;
        if (keys.left) player.x -= player.speed;
    } else if (player.isJumping) {
        player.x += player.velocityX;
        player.velocityX *= 0.98;
    }

    // 양 옆 벽 충돌 체크 및 튕김 효과
    if (player.x < 0) {
        player.x = 0;
        if (player.isJumping) {
            player.velocityX = Math.abs(player.velocityX) * player.bounceForce;
            player.velocityY *= player.friction;
        } else {
            player.velocityX = 0;
        }
    }
    if (player.x + player.width > canvas.width) {
        player.x = canvas.width - player.width;
        if (player.isJumping) {
            player.velocityX = -Math.abs(player.velocityX) * player.bounceForce;
            player.velocityY *= player.friction;
        } else {
            player.velocityX = 0;
        }
    }

    // 플랫폼 충돌 체크
    platforms.forEach(platform => {
        if (checkCollision(player, platform)) {
            if (player.velocityY > 0 && 
                player.y + player.height - player.velocityY <= platform.y) {
                player.y = platform.y - player.height;
                player.velocityY = 0;
                player.velocityX *= player.friction;
                player.isJumping = false;
            }
        }
    });

    // 카메라 업데이트
    const targetCameraY = player.y - (canvas.height / 2);
    camera.y += (targetCameraY - camera.y) * 0.1;
    camera.y = Math.max(0, Math.min(camera.y, WORLD_HEIGHT - canvas.height));
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    ctx.save();
    ctx.translate(0, -camera.y);

    // 배경 그리기
    if (backgroundImage.complete) {
        // 배경 이미지를 타일처럼 반복해서 그리기
        const bgWidth = canvas.width;
        const bgHeight = canvas.height;
        
        // 현재 카메라 위치를 기반으로 필요한 배경 타일 계산
        const startY = Math.floor(camera.y / bgHeight) * bgHeight;
        const endY = Math.ceil((camera.y + canvas.height) / bgHeight) * bgHeight;
        
        // 보이는 영역에 배경 타일 그리기
        for (let y = startY; y < endY; y += bgHeight) {
            ctx.drawImage(backgroundImage, 0, y, bgWidth, bgHeight);
        }
    }

    // 플랫폼 그리기 - 각 타입별로 다른 모양/색상
    platforms.forEach(platform => {
        switch(platform.type) {
            case 'start':
                ctx.fillStyle = '#8B4513';  // 갈색 시작점
                break;
            case 'clue':
                ctx.fillStyle = '#DEB887';  // 책장같은 베이지색
                break;
            case 'photo':
                ctx.fillStyle = '#DAA520';  // 금색 사진 조각
                break;
            case 'museum':
                ctx.fillStyle = '#8B0000';  // 붉은색 박물관 입구
                break;
        }
        ctx.fillRect(platform.x, platform.y, platform.width, platform.height);
    });

    // 페페 그리기
    if (player.image.complete) {
        if (player.direction === -1) {
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(player.image, -player.x - player.width, player.y, player.width, player.height);
            ctx.restore();
        } else {
            ctx.drawImage(player.image, player.x, player.y, player.width, player.height);
        }
    }

    // 단서에 가까이 갔을 때 텍스트 표시
    platforms.forEach((platform, index) => {
        if (platform.type === 'clue' && 
            Math.abs(player.x - platform.x) < 50 && 
            Math.abs(player.y - platform.y) < 50) {
            ctx.fillStyle = 'white';
            ctx.font = '14px Arial';
            ctx.fillText(clues[`clue${index}`], platform.x, platform.y - 20);
        }
    });

    ctx.restore();

    // 게이지 그리기
    if (player.isCharging) {
        const gaugeWidth = (player.jumpForce / player.maxJumpForce) * 50;
        ctx.fillStyle = 'yellow';
        ctx.fillRect(player.x, player.y - camera.y - 15, gaugeWidth, 5);
    }

    // draw 함수의 미니맵 부분 수정
    if (showMinimap) {
        // 미니맵 배경
        ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
        ctx.fillRect(canvas.width - MINIMAP_WIDTH - 10, 10, MINIMAP_WIDTH, MINIMAP_HEIGHT);

        // 플랫폼 그리기
        const minimapScale = MINIMAP_HEIGHT / 7000;  // 마지막 발판 높이에 맞춰 조정
        platforms.forEach(platform => {
            // 미니맵 내부에 플랫폼이 표시되도록 위치 조정
            const minimapX = Math.min(
                Math.max(
                    canvas.width - MINIMAP_WIDTH - 10 + (platform.x * MINIMAP_WIDTH / canvas.width),
                    canvas.width - MINIMAP_WIDTH - 10
                ),
                canvas.width - 10
            );
            
            ctx.fillStyle = 'white';
            ctx.fillRect(
                minimapX,
                10 + (platform.y * minimapScale),
                Math.min((platform.width * MINIMAP_WIDTH / canvas.width), MINIMAP_WIDTH),
                2
            );
        });

        // 플레이어 위치 표시 - 미니맵 내부로 제한
        const playerMinimapX = Math.min(
            Math.max(
                canvas.width - MINIMAP_WIDTH - 10 + (player.x * MINIMAP_WIDTH / canvas.width),
                canvas.width - MINIMAP_WIDTH - 10
            ),
            canvas.width - 10
        );
        
        ctx.fillStyle = 'red';
        ctx.fillRect(
            playerMinimapX,
            10 + (player.y * minimapScale),
            4,
            4
        );
    }
}

function gameLoop() {
    // 이전 위치 저장
    player.prevY = player.y;
    
    update();
    draw();
    requestAnimationFrame(gameLoop);
}

function resetToStart() {
    player.x = 500; // 시작 지점 x 좌표
    player.y = WORLD_HEIGHT - 70; // 시작 지점 y 좌표
    player.velocityX = 0;
    player.velocityY = 0;
    player.isJumping = false;
}

gameLoop();
</script>
</body>
</html>