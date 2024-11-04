let currentPage = 0;
const cardsPerPage = 20;
const totalCards = 40;
const totalPages = Math.ceil(totalCards / cardsPerPage);
let collectedCount = 0; // 현재까지 수집한 경험치
let userNickname = ""; 

window.onload = function() {
    // 사용자 정보와 랭킹 정보를 가져옵니다.
    fetchUserInfo();
    fetchRanking();
    
    const challengeGrid = document.getElementById('challengeGrid');
    for (let p = 0; p < totalPages; p++) {
        const page = document.createElement('div');
        page.className = 'page';
        for (let i = 1 + p * cardsPerPage; i <= cardsPerPage * (p + 1) && i <= totalCards; i++) {
            const card = document.createElement('div');
            card.className = 'card';
            card.dataset.id = i;
            card.onclick = () => revealGame(card, `game${i}`);
            
            const cardInner = document.createElement('div');
            cardInner.className = 'card-inner';
            
            const cardFront = document.createElement('div');
            cardFront.className = 'card-front';
            cardFront.innerText = i;
            
            const cardBack = document.createElement('div');
            cardBack.className = 'card-back';
            const img = document.createElement('img');
            img.src = `./img/monster_image${i}.jpg`;
            img.alt = `카드 ${i} 해결된 이미지`;
            cardBack.appendChild(img);
            
            cardInner.appendChild(cardFront);
            cardInner.appendChild(cardBack);
            card.appendChild(cardInner);
            page.appendChild(card);
        }
        challengeGrid.appendChild(page);
    }
};

// 사용자 정보를 가져오는 함수 추가
function fetchUserInfo() {
    fetch('./php/user_info.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Fetched user info:', data); // 사용자 정보 로그
            if (data.username) {
                userNickname = data.username; // userNickname 변수에 사용자 이름 저장
                document.getElementById('user-nickname').innerText = userNickname; // 닉네임 업데이트
            }
        })
        .catch(error => {
            console.error('Error fetching user info:', error);
        });
}

function fetchRanking() {
    fetch('./php/ranking.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayRanking(data.rankings);
            } else {
                console.error('Error fetching rankings:', data.error);
            }
        })
        .catch(error => {
            console.error('Error fetching ranking:', error);
        });
}

function displayRanking(rankings) {
    const rankingList = document.getElementById('rankingList');
    rankingList.innerHTML = ''; // 기존 내용을 지웁니다.

    rankings.forEach((ranking, index) => {
        const listItem = document.createElement('li');
        listItem.textContent = `${index + 1}. ${ranking.username} - 점수: ${ranking.score}, 스테이지: ${ranking.stage}`;
        rankingList.appendChild(listItem);
    });
}

function showPage(pageIndex) {
    const challengeGrid = document.getElementById('challengeGrid');
    challengeGrid.style.transform = `translateX(-${pageIndex * 100}vw)`;
}

function nextPage() {
    currentPage = Math.min(currentPage + 1, totalPages - 1);
    showPage(currentPage);
}

function prevPage() {
    currentPage = Math.max(currentPage - 1, 0);
    showPage(currentPage);
}

function revealGame(cardElement, gameId) {
    const popup = document.getElementById('gamePopup');
    popup.style.display = 'flex';
    document.getElementById('gameContent').innerText = `게임: ${gameId}`;
    popup.dataset.currentCardId = cardElement.dataset.id;
}

function closePopup() {
    document.getElementById('gamePopup').style.display = 'none';
}

function solveGame() {
    const popup = document.getElementById('gamePopup');
    const cardId = popup.dataset.currentCardId;
    const card = document.querySelector(`[data-id="${cardId}"]`);

    if (card && !card.classList.contains('solved')) {
        card.classList.add('solved');
        const cardInner = card.querySelector('.card-inner');
        cardInner.style.transform = "rotateY(180deg)";
        
        const cardBack = card.querySelector('.card-back');
        cardBack.innerHTML = `<img src="./img/monster_image${cardId}.jpg" alt="몬스터 이미지" style="width:100%; height:100%;">`;

        // 경험치 증가
        collectedCount++;
        document.getElementById('collectedText').innerText = `${collectedCount} / ${totalCards}`;

        const progressFill = document.getElementById('progressFill');
        progressFill.style.width = `${(collectedCount / totalCards) * 100}%`; // 진행률 바 업데이트

        // 서버에 게임 요청 보내기
        fetch('./php/set_game_request.php', {
            method: 'POST',
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.ok) {
                // 이후 점수 업데이트 요청
                fetchScoreUpdate();
            }
        })
        .catch(error => console.error('Error setting game request:', error));
    }

    closePopup();
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const leftArrowButton = document.querySelector('.arrow-button.left');
    sidebar.classList.toggle('active');
    
    if (sidebar.classList.contains('active')) {
        sidebarToggle.style.left = '270px'; // 사이드바 열렸을 때 토글 버튼 위치
        leftArrowButton.style.left = '270px'; // 사이드바 열렸을 때 왼쪽 화살표 버튼 위치
    } else {
        sidebarToggle.style.left = '20px'; // 사이드바 닫혔을 때 토글 버튼 위치
        leftArrowButton.style.left = '20px'; // 사이드바 닫혔을 때 왼쪽 화살표 버튼 위치
    }
}

function fetchScoreUpdate() {
    fetch('./php/Scoreboard2.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            alert(data.error); // 사용자에게 오류 메시지 표시
        } else if (data.message) {
            console.log(data.message);
            updateScoreboard(data.score, data.stage);
        }
    })
    .catch(error => console.error('Error parsing JSON:', error));
}

function logout() {
    alert("로그아웃되었습니다.");
    window.location.href = "login.html"; 
}
