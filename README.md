# Meta Boxes

워드프레스 메타 박스를 추가/제어하는 설정을 지원합니다.

## 설치

`composer require bojagi/meta-boxes`

## 사용법

아래처럼 설정을 배열로 입력하거나

```php
use Bojaghi\MetaBoxes\MetaBoxes;

new MetaBoxes([ /* ... setup ... */]);
```

설정을 리턴하는 파일 경로를 입력하세요.

```php
new MetaBoxes('/path/to/setup/file');
```

MetaBoxes 클래스를 인스턴스화 하면 알아서 'do_meta_boxes' 훅의 콜백을 추가합니다.

## 설정

설정의 배열은 아래와 같습니다.

```php
[
    'add'      => [ /* 추가할 메타 박스 */ ],
    'remove'   => [ /* 제거할 메타 박스 */ ],
    'continy'  => null, // 또는 Continy 객체
    'priority' => 10,   // 'do_meta_boxes' 액션 콜백의 우선순위값. 기본 10.
]
```

#### add

추가할 메타 박스 목록입니다. 개별 항목의 포맷은 아래와 같습니다. `add_meta_box` 함수의 인수와 동일한 형태입니다.

```php
[
    'id'            => '',                       // Required
    'title'         => '',                       // Required
    'callback'      => '',                       // Required
    'screen'        => null,                     // Optional
    'context'       => static::CONTEXT_ADVANCED, // Optional
    'priority'      => static::PRIORITY_DEFAULT, // Optional
    'callback_args' => null,                     // Optional
]
```

#### remove

제거할 메타 박스 목록입니다. 개별 항목의 포맷은 아래와 같습니다. `remove_meta_box` 함수의 인수와 동일한 형태입니다.

```php
[
    'id'      => '',       // Required
    'screen'  => null,     // Required
    'context' => 'normal', // Required
]
```