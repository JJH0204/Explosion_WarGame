# Flame WarGame

Flame WarGame은 웹 보안 학습을 위한 실전형 워게임 플랫폼입니다. 40개 이상의 단계별 문제를 통해 다양한 웹 취약점을 학습하고 실습할 수 있습니다.

## 주요 기능

### 1. 단계별 학습 시스템
- 총 40개 이상의 웹 해킹 문제 제공
- 난이도별로 구성된 체계적인 학습 경로
- 실전과 유사한 환경에서의 취약점 실습

### 2. 사용자 시스템
- 회원가입 및 로그인 기능
- 사용자별 진행 상황 추적
- 관리자 페이지를 통한 시스템 관리

### 3. 기술 스택
- 프론트엔드: HTML, CSS, JavaScript
- 백엔드: Apache HTTP Server
- 보안: .htaccess를 통한 접근 제어

## 프로젝트 구조
```
Flame_WarGame/
├── Web/                      # 웹 애플리케이션 루트
│   ├── Question/            # 문제 파일들
│   ├── assets/             # 정적 파일 (이미지, CSS, JS)
│   ├── data/              # 데이터 파일
│   ├── index.html         # 로그인 페이지
│   ├── flame.html         # 메인 페이지
│   └── flameadmin.html    # 관리자 페이지
└── setup_script/           # 환경 설정 스크립트 
```

## 설치 및 실행
1. Apache HTTP Server 설치
2. 프로젝트 파일을 `/usr/share/httpd/Flame_WarGame` 디렉토리에 복사
3. Apache 설정 파일에서 해당 디렉토리를 웹 루트로 설정
4. setup_script 디렉토리의 스크립트 실행

## 보안 설정
- 각 문제 디렉토리는 .htaccess를 통해 접근이 제한됨
- 문제 파일은 직접 접근이 불가능하며, 적절한 인증을 통해서만 접근 가능
- 관리자 페이지는 별도의 인증 시스템을 통해 보호됨

## 개발 및 유지보수
본 프로젝트는 Team Firewall에서 개발 및 관리하고 있습니다. 보안 취약점이나 버그를 발견하시면 관리자에게 보고해 주시기 바랍니다.
