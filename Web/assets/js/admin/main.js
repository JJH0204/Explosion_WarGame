document.addEventListener('DOMContentLoaded', function() {
    class AdminCardManager {
        constructor() {
            // localStorage에서 페이지 번호 가져오기
            const savedPage = localStorage.getItem('currentCardPage');
            
            this.state = {
                currentPage: savedPage ? parseInt(savedPage) : 0,
                cardsPerPage: 10,
                totalCards: 40,
                totalPages: Math.ceil(40 / 10),
                clearedStages: []
            };

            this.elements = {
                cardsWrapper: document.querySelector('.cards-wrapper'),
                popup: document.getElementById('challengePopup'),
                progressFill: document.querySelector('.progress-fill'),
                completedElement: document.getElementById('completed-challenges'),
                totalElement: document.getElementById('total-challenges')
            };

            // localStorage에서 클리어한 스테이지 정보도 가져오기
            const savedClearedStages = localStorage.getItem('clearedStages');
            this.state.clearedStages = savedClearedStages ? JSON.parse(savedClearedStages) : [];

            this.setupPopupEvents();

            // 관리자 모드 키 조합 감지 (Ctrl + Shift + A)
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.shiftKey && e.key === 'A') {
                    this.openAdminMode();
                }
            });

            // localStorage 변경 감지
            window.addEventListener('storage', this.checkStorageModification.bind(this));
            // 초기 로드 시에도 체크
            this.checkStorageModification();
        }

        checkStorageModification() {
            const clearedStages = JSON.parse(localStorage.getItem('clearedStages') || '[]');
            const allStages = Array.from({length: 40}, (_, i) => i + 1);
            
            // 모든 스테이지가 클리어된 것으로 표시되었는지 확인
            const isAllCleared = allStages.every(stage => clearedStages.includes(stage));
            
            if (isAllCleared) {
                // DB에서 실제 클리어한 스테이지 수 확인
                fetch('/assets/php/get_cleared_stages.php')
                    .then(response => response.json())
                    .then(result => {
                        if (result.success && result.data.length < 40) {
                            // 실제로는 모든 스테이지를 클리어하지 않았는데 localStorage가 수정된 경우
                            alert('축하합니다! 플래그를 찾았습니다: Flag{LocalStorage_Modification}');
                        }
                    })
                    .catch(error => console.error('Error checking stages:', error));
            }
        }

        async init() {
            try {
                await this.loadClearedStages();
                this.renderCards();
                this.setupArrowButtons();
                this.createPageButtons();
                this.updateProgress();
                
                // 저장된 페이지로 즉시 이동
                const savedPage = parseInt(localStorage.getItem('currentCardPage')) || 0;
                this.setCurrentPage(savedPage);
            } catch (error) {
                console.error('초기화 실패:', error);
            }
        }

        async loadClearedStages() {
            try {
                const response = await fetch('/assets/php/get_cleared_stages.php');
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.error);
                }

                // localStorage에 저장된 기존 데이터 가져오기
                const localStages = JSON.parse(localStorage.getItem('clearedStages') || '[]');
                
                // DB의 데이터와 로컬 데이터 합치기 (중복 제거)
                const mergedStages = [...new Set([...result.data, ...localStages])].sort((a, b) => a - b);
                
                // state와 localStorage 업데이트
                this.state.clearedStages = mergedStages;
                localStorage.setItem('clearedStages', JSON.stringify(mergedStages));
            } catch (error) {
                console.error('Error loading cleared stages:', error);
                // 에러 발생 시 localStorage의 데이터만이라도 사용
                const localStages = JSON.parse(localStorage.getItem('clearedStages') || '[]');
                this.state.clearedStages = localStages;
            }
        }

        renderCards() {
            if (!this.elements.cardsWrapper) return;
            
            this.elements.cardsWrapper.innerHTML = '';
            
            for (let page = 0; page < this.state.totalPages; page++) {
                const grid = document.createElement('div');
                grid.className = 'cards-grid';
                
                const startCard = page * this.state.cardsPerPage + 1;
                const endCard = Math.min((page + 1) * this.state.cardsPerPage, this.state.totalCards);
                
                for (let i = startCard; i <= endCard; i++) {
                    grid.appendChild(this.createCard(i));
                }
                
                this.elements.cardsWrapper.appendChild(grid);
            }
        }

        createCard(cardNumber) {
            const card = document.createElement('div');
            card.className = 'challenge-card';

            const cardInner = document.createElement('div');
            cardInner.className = 'challenge-card-inner';

            const img = document.createElement('img');
            img.src = this.state.clearedStages.includes(cardNumber) 
                ? `/assets/images/monsters/monster_image${cardNumber}.png`
                : '/assets/images/flame_card.jpg';
            img.alt = `Card ${cardNumber}`;

            cardInner.appendChild(img);
            card.appendChild(cardInner);

            card.addEventListener('click', async () => {
                try {
                    // config.json 불러오기
                    const configResponse = await fetch('/data/config.json');
                    const config = await configResponse.json();
                    
                    // 해당 카드의 챌린지 정보 찾기
                    const challengeInfo = config.challenges.find(c => c.id === cardNumber);
                    if (!challengeInfo) {
                        throw new Error('Challenge not found');
                    }

                    // 마크다운 파일 불러오기
                    const markdownResponse = await fetch(`/data/challenges/mk_${cardNumber}.md`);
                    if (!markdownResponse.ok) {
                        throw new Error('마크다운 파일을 불러올 수 없습니다.');
                    }
                    const markdownContent = await markdownResponse.text();

                    const popup = document.getElementById('challengePopup');
                    
                    // 팝업 내용 구성
                    popup.innerHTML = `
                        <div class="challenge-popup-content">
                            <button class="close-button">&times;</button>
                            <h2 class="challenge-title">Challenge ${cardNumber}</h2>
                            
                            <div class="tags-container">
                                <span class="tag category-tag">${config.categories[challengeInfo.category].name}</span>
                                <span class="tag difficulty-tag">${config.difficulty[challengeInfo.difficulty].name}</span>
                                <span class="tag points-tag">${config.difficulty[challengeInfo.difficulty].points}pt</span>
                            </div>
                            
                            <div class="markdown-content">
                                ${marked.parse(markdownContent)}
                            </div>
                            
                            <div class="flag-input-container">
                                <input type="text" 
                                    id="flagInput-${cardNumber}" 
                                    name="flagInput" 
                                    class="flag-input" 
                                    placeholder="플래그를 입력하세요">
                                <button class="action-button submit">제출</button>
                            </div>
                            
                            <button class="action-button challenge">문제 풀기</button>
                        </div>
                    `;
                    
                    popup.style.display = 'flex';
                    
                    // 닫기 버튼 이벤트
                    const closeButton = popup.querySelector('.close-button');
                    closeButton.addEventListener('click', () => {
                        popup.style.display = 'none';
                    });
                    
                    // 제출 버튼 이벤트
                    const submitButton = popup.querySelector('.action-button.submit');
                    if (submitButton) {
                        submitButton.addEventListener('click', async () => {
                            const flagInput = popup.querySelector(`#flagInput-${cardNumber}`);
                            if (!flagInput) return;

                            try {
                                // config.json에서 해당 문제의 정답 확인
                                const challengeInfo = config.challenges.find(c => c.id === cardNumber);
                                if (!challengeInfo) {
                                    throw new Error('Challenge not found');
                                }

                                // 입력된 플래그와 정답 비교
                                if (flagInput.value === challengeInfo.answer) {
                                    // 정답이 맞으면 saveClearedCard.php 호출
                                    const response = await fetch('/assets/php/saveClearedCard.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: `cardId=${cardNumber}`
                                    });

                                    const result = await response.json();
                                    if (result.success) {
                                        // DB에서 실제 클리어한 스테이지 목록을 가져와서 localStorage 초기화
                                        const clearedResponse = await fetch('/assets/php/get_cleared_stages.php');
                                        const clearedResult = await clearedResponse.json();
                                        
                                        if (clearedResult.success) {
                                            // DB의 실제 클리어 상태로 localStorage 초기화
                                            localStorage.setItem('clearedStages', JSON.stringify(clearedResult.data));
                                        }
                                        
                                        alert('축하합니다! 문제를 해결했습니다!');
                                        location.reload();
                                    } else {
                                        alert(result.error || '오류가 발생했습니다.');
                                    }
                                } else {
                                    alert('틀린 답입니다. 다시 시도해주세요.');
                                }
                            } catch (error) {
                                console.error('Error submitting flag:', error);
                                alert('플래그 제출 중 오류가 발생했습니다.');
                            }
                        });
                    }

                    // 챌린지 버튼 이벤트
                    const challengeButton = popup.querySelector('.action-button.challenge');
                    if (challengeButton) {
                        challengeButton.addEventListener('click', () => {
                            const questionUrl = `/Question/question${cardNumber}/question${cardNumber}.html`;
                            window.open(questionUrl, '_blank');
                        });
                    }
                } catch (error) {
                    console.error('Error loading challenge:', error);
                    alert('챌린지 로딩 중 오류가 발생했습니다.');
                }
            });
            
            return card;
        }

        setupArrowButtons() {
            const leftButton = document.querySelector('.arrow-button.left');
            const rightButton = document.querySelector('.arrow-button.right');
            const cardsWrapper = document.querySelector('.cards-wrapper');

            if (leftButton) {
                leftButton.addEventListener('click', () => {
                    if (this.state.currentPage > 0) {
                        this.state.currentPage--;
                        cardsWrapper.style.transform = `translateX(-${this.state.currentPage * 100}%)`;
                        this.updateButtons();
                        // localStorage에 현재 페이지 저장
                        localStorage.setItem('currentCardPage', this.state.currentPage);
                    }
                });
            }

            if (rightButton) {
                rightButton.addEventListener('click', () => {
                    if (this.state.currentPage < this.state.totalPages - 1) {
                        this.state.currentPage++;
                        cardsWrapper.style.transform = `translateX(-${this.state.currentPage * 100}%)`;
                        this.updateButtons();
                        // localStorage에 현재 페이지 저장
                        localStorage.setItem('currentCardPage', this.state.currentPage);
                    }
                });
            }

            this.updateButtons();
        }

        updateButtons() {
            const leftButton = document.querySelector('.arrow-button.left');
            const rightButton = document.querySelector('.arrow-button.right');

            if (leftButton) {
                if (this.state.currentPage === 0) {
                    leftButton.style.opacity = '0.5';
                    leftButton.style.pointerEvents = 'none';
                } else {
                    leftButton.style.opacity = '1';
                    leftButton.style.pointerEvents = 'auto';
                }
            }

            if (rightButton) {
                if (this.state.currentPage === this.state.totalPages - 1) {
                    rightButton.style.opacity = '0.5';
                    rightButton.style.pointerEvents = 'none';
                } else {
                    rightButton.style.opacity = '1';
                    rightButton.style.pointerEvents = 'auto';
                }
            }

            this.updatePageButtons();
        }

        createPageButtons() {
            const container = document.querySelector('.page-buttons');
            if (!container) return;

            container.innerHTML = '';
            
            for (let i = 0; i < this.state.totalPages; i++) {
                const button = document.createElement('button');
                button.className = 'page-button' + (i === this.state.currentPage ? ' active' : '');
                // button.textContent = i + 1;
                
                button.addEventListener('click', () => {
                    this.state.currentPage = i;
                    const wrapper = document.querySelector('.cards-wrapper');
                    wrapper.style.transform = `translateX(-${i * 100}%)`;
                    // localStorage에 현재 페이지 저장
                    localStorage.setItem('currentCardPage', i);
                    this.updateButtons(); // 화살표 버튼 상태 업데이트 추가
                    this.updatePageButtons();
                });
                
                container.appendChild(button);
            }
        }

        updatePageButtons() {
            const pageButtons = document.querySelectorAll('.page-button');
            pageButtons.forEach((button, index) => {
                if (index === this.state.currentPage) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        updateProgress() {
            const progressFill = document.querySelector('.progress-fill');
            if (!progressFill) {
                console.error('Progress fill element not found');
                return;
            }

            const completedElement = document.getElementById('completed-challenges');
            if (!completedElement) {
                console.error('Completed challenges element not found');
                return;
            }

            const validStage = (!isNaN(this.state.currentPage) && this.state.currentPage !== null && this.state.currentPage !== '') ? parseInt(this.state.currentPage) : 0;
            
            const totalStages = 40;
            completedElement.textContent = validStage || '-';  // 유효한 값이 없으면 '-' 표시
            progressFill.style.width = `${(validStage / totalStages) * 100}%`;  // 유효하지 않으면 0%
        }

        updateCardImage(cardNumber) {
            const cards = document.querySelectorAll('.challenge-card');
            cards.forEach(card => {
                if (parseInt(card.dataset.cardNumber) === cardNumber) {
                    const img = card.querySelector('img');
                    if (img) {
                        img.src = `/assets/images/monsters/monster_image${cardNumber}.png`;
                        img.alt = `Monster ${cardNumber}`;
                    }
                }
            });
        }

        setupCardClickEvents() {
            const cards = document.querySelectorAll('.challenge-card');
            cards.forEach((card, index) => {
                card.addEventListener('click', () => {
                    this.openPopup(index + 1);
                });
            });
        }

        initializeCompletedChallenges() {
            const completedChallenges = JSON.parse(localStorage.getItem('completedChallenges') || '[]');
            completedChallenges.forEach(challengeNumber => {
                this.updateCardImage(challengeNumber);
            });
            this.updateProgress(0); // 진행 상황 초기화
        }

        setupPopupEvents() {
            const popup = document.getElementById('challengePopup');
            
            // 팝업 외부 클릭 시 닫기
            popup.addEventListener('click', (e) => {
                // 팝업의 배경(overlay)을 클릭했을 때만 닫기
                if (e.target === popup) {
                    popup.style.display = 'none';
                }
            });

            // ESC 키를 눌렀을 때도 팝업 닫기 (선택적)
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && popup.style.display === 'flex') {
                    popup.style.display = 'none';
                }
            });
        }

        setCurrentPage(pageNumber) {
            // 유효한 페이지 번호인지 확인
            if (pageNumber >= 0 && pageNumber < this.state.totalPages) {
                this.state.currentPage = pageNumber;
                localStorage.setItem('currentCardPage', pageNumber);
                
                // 카드 컨테이너 이동
                if (this.elements.cardsWrapper) {
                    this.elements.cardsWrapper.style.transform = `translateX(-${pageNumber * 100}%)`;
                }
                
                // 페이지 버튼 업데이트
                this.updatePageButtons();
            }
        }

        openAdminMode() {
            const currentStages = JSON.parse(localStorage.getItem('clearedStages') || '[]');
            const input = prompt('클리어한 스테이지 번호를 쉼표로 구분하여 입력하세요 (예: 1,2,3,4)');
            
            if (input === null) return; // 취소 버튼 클릭 시

            try {
                // 입력값을 숫자 배열로 변환
                const newStages = input.split(',')
                    .map(num => parseInt(num.trim()))
                    .filter(num => !isNaN(num) && num > 0 && num <= this.state.totalCards);

                // 중복 제거 및 정렬
                const uniqueStages = [...new Set(newStages)].sort((a, b) => a - b);
                
                // localStorage 업데이트
                localStorage.setItem('clearedStages', JSON.stringify(uniqueStages));
                
                // state 업데이트
                this.state.clearedStages = uniqueStages;
                
                // 카드 다시 렌더링
                this.renderCards();
                
                alert('클리어 상태가 업데이트되었습니다.');
            } catch (error) {
                console.error('Error updating cleared stages:', error);
                alert('올바른 형식으로 입력해주세요.');
            }
        }
    }

    async function updateRanking() {
        try {
            const response = await fetch('/assets/php/ranking.php');
            const data = await response.json();
            
            if (!data.success) {
                console.error('Failed to fetch ranking data:', data.error);
                return;
            }

            const rankingList = document.getElementById('rankingList');
            if (!rankingList) {
                console.error('Ranking list element not found');
                return;
            }
            
            rankingList.innerHTML = '';
            
            data.rankings.slice(0, 7).forEach((player, index) => {
                const li = document.createElement('li');
                li.className = 'ranking-item';
                
                let rankDisplay;
                if (index < 3) {
                    const medals = ['🥇', '🥈', '🥉'];
                    rankDisplay = medals[index];
                } else {
                    rankDisplay = index + 1;
                }
                
                li.innerHTML = `
                    <span class="rank">${rankDisplay}</span>
                    <div class="player-info">
                        <span class="nickname">${player.nickname}</span>
                        <span class="score">${player.score}pt</span>
                    </div>
                `;
                rankingList.appendChild(li);
            });
        } catch (error) {
            console.error('Error fetching ranking data:', error);
        }
    }

    async function updateUserInfo() {
        try {
            const response = await fetch('/assets/php/user_info.php');
            const data = await response.json();
            
            if (data.success) {
                const characterImage = document.querySelector('.character-image');
                if (characterImage) {
                    // 기존 랭크 클래스와 transition style 제거
                    characterImage.classList.remove('rank-1', 'rank-2', 'rank-3');
                    characterImage.style.transition = 'all 0.3s ease';
                    
                    // 현재 랭킹에 따라 적절한 클래스 추가
                    const rank = parseInt(data.data.rank);
                    if (rank === 1) {
                        characterImage.classList.add('rank-1');
                    } else if (rank === 2) {
                        characterImage.classList.add('rank-2');
                    } else if (rank === 3) {
                        characterImage.classList.add('rank-3');
                    }
                }
                // ... 나머지 드 ...
            }
        } catch (error) {
            console.error('Error updating user info:', error);
        }
    }

    // 팝업 이벤트 설정
    function setupPopups() {
        // 플래그 버튼
        const flagBtn = document.getElementById('flagBtn');
        const flagPopup = document.getElementById('flagPopup');
        const cancelFlag = document.getElementById('cancelFlag');

        if (flagBtn && flagPopup) {
            flagBtn.addEventListener('click', () => flagPopup.style.display = 'flex');
        }
        if (cancelFlag) {
            cancelFlag.addEventListener('click', () => flagPopup.style.display = 'none');
        }

        // 이벤트 버튼
        const eventBtn = document.getElementById('EventBtn');
        const eventPopup = document.getElementById('eventPopup');
        const closeEvent = document.getElementById('closeEvent');

        if (eventBtn && eventPopup) {
            eventBtn.addEventListener('click', () => {
                eventPopup.style.display = 'flex';
            });
        }
        if (closeEvent) {
            closeEvent.addEventListener('click', () => {
                eventPopup.style.display = 'none';
            });
        }

        // 로그아웃 버튼
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                if (confirm('로그아웃 하시겠습니까?')) {
                    window.location.href = 'index.html';
                }
            });
        }
    }

    // 초기화
    const adminCardManager = new AdminCardManager();
    adminCardManager.init();
    setupPopups();
    updateUserInfo();
    updateRanking();
    setInterval(updateRanking, 180000);

    // user_info.php에서 데이터를 가져와서 진행 상태 업데이트
    fetch('/assets/php/user_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stage = parseInt(data.data.stage);
                const progressFill = document.querySelector('.progress-fill');
                const completedElement = document.getElementById('completed-challenges');
                
                if (progressFill && completedElement) {
                    completedElement.textContent = stage;
                    progressFill.style.width = `${(stage / 40) * 100}%`;
                }
            }
        })
        .catch(error => console.error('Error:', error));

    // 로고 버튼 클릭 이벤트 추가
    const logoButton = document.querySelector('.logo-button');
    if (logoButton) {
        logoButton.addEventListener('click', () => {
            window.location.reload();
        });
    }
}); 