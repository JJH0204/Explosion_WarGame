# 문제 17 - 지렁이 게임

## 문제 설명
이 문제는 사용자가 지렁이 게임을 진행하며 10단계에 도달하여 플래그를 획득하는 도전 과제입니다. 지렁이를 움직여 음식을 먹고 점수를 쌓으며, 충돌을 피하면서 진행해야 합니다. 점수가 10점에 도달하면 서버에서 플래그를 반환합니다.

## 풀이 과정

### 1. 문제 조건 분석
- 캔버스 위에서 지렁이를 움직이며 음식을 먹습니다.
- 화살표 키를 사용하여 지렁이의 방향을 조작합니다.
- Snake가 벽에 부딪히거나 자기 자신과 충돌하면 게임이 종료됩니다.
- 점수는 음식을 먹을 때마다 1점씩 증가합니다.
- 점수가 10점에 도달하면 플래그를 반환받아 문제를 해결합니다.

### 2. 게임 조작 방법
1. 화살표 키를 사용하여 지렁이의 이동 방향을 조작합니다.
2. 음식을 먹어 점수를 획득합니다.
3. 10점을 획득할 때까지 진행합니다.

### 3. 제출 및 검증
1. 점수가 10점에 도달하면 게임이 종료됩니다.
2. 서버에서 플래그가 반환됩니다.
3. 반환된 플래그를 확인하여 문제를 해결합니다.

### 4. 사용된 기술
- 게임 구현: HTML Canvas와 JavaScript를 사용하여 지렁이 게임을 구현.
- 서버와 통신: 점수 10점에 도달하면 PHP로 구현된 서버에서 플래그를 반환.
- 클라이언트-서버 통신: JavaScript의 `fetch` API를 사용하여 서버와 통신.

### 최종 플래그
```
FLAG{snake_master}
```