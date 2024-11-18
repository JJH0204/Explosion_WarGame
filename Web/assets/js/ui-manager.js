class UIManager {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.mainContent = document.querySelector('.main-content');
        this.sidebarToggle = document.querySelector('.sidebar-toggle');
        this.leftArrowButton = document.querySelector('.arrow-button.left');
        
        
        this.initializeSidebar();
        this.initializeResponsiveLayout();
        
        window.addEventListener('resize', () => this.handleResize());
    }

    initializeSidebar() {
        if (this.sidebarToggle && this.sidebar && this.mainContent) {
            this.sidebarToggle.addEventListener('click', () => {
                this.sidebar.classList.toggle('active');
                this.mainContent.classList.toggle('sidebar-active');
                
                // 왼쪽 화살표 버튼 위치 조정
                if (this.leftArrowButton) {
                    const isActive = this.sidebar.classList.contains('active');
                    this.leftArrowButton.style.left = isActive ? '270px' : '20px';
                    
                    // 모바일 대응
                    if (window.innerWidth <= 768) {
                        this.leftArrowButton.style.left = isActive ? '220px' : '20px';
                    }
                }
            });
        }
    }

    updateArrowButtonState() {
        if (this.sidebar.classList.contains('active')) {
            this.leftArrowButton.style.opacity = '1';
            this.leftArrowButton.style.pointerEvents = 'auto';
        } else {
            this.leftArrowButton.style.opacity = '0.3';
            this.leftArrowButton.style.pointerEvents = 'none';
        }
    }

    initializeResponsiveLayout() {
        this.adjustLayout();
    }

    adjustLayout() {
        if (!this.sidebar || !this.mainContent) return;

        const windowWidth = window.innerWidth;
        
        if (!this.sidebar.classList.contains('active')) {
            this.mainContent.style.transform = 'none';
            return;
        }

        if (windowWidth >= 1921) {
            this.mainContent.style.transform = 'none';
        } else {
            const offset = windowWidth <= 768 ? '75px' : 
                          windowWidth <= 992 ? '100px' : '125px';
            this.mainContent.style.transform = `translateX(${offset})`;
        }
    }

    handleResize() {
        this.adjustLayout();
        
        // 화면 크기 변경 시 왼쪽 화살표 위치 조정
        if (this.leftArrowButton && this.sidebar.classList.contains('active')) {
            this.leftArrowButton.style.left = window.innerWidth <= 768 ? '220px' : '270px';
        }
    }

    isSidebarActive() {
        return this.sidebar?.classList.contains('active') || false;
    }

    getMainContent() {
        return this.mainContent;
    }

    // 추가된 fetchUserInfo 메서드
    async fetchUserInfo() {
        try {
            const response = await fetch('./assets/php/user_info.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
    
            if (data.success && data.data) {
                // 유저 정보를 UI에 반영
                const nicknameElement = document.getElementById('player-nickname');
                if (nicknameElement) {
                    nicknameElement.textContent = data.data.nickname;
                } else {
                    console.error('Nickname element not found');
                }
            } else {
                console.error('Failed to fetch user info:', data.error);
            }
        } catch (error) {
            console.error('Error fetching user info:', error);
        }
    }
    

    // 추가된 fetchRanking 메서드 (랭킹 정보 가져오기 예제)
    async fetchRanking() {
        try {
            const response = await fetch('./assets/php/ranking.php');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const rankingInfo = await response.json();
            console.log('Ranking Info:', rankingInfo);
            // 랭킹 정보를 UI에 반영하는 코드 추가
        } catch (error) {
            console.error('Error fetching ranking info:', error);
        }
    }
} 

    

