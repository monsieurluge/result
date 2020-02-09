# Result

![logo](logo.png)

The goal of the Result library is to say goodbye to the `if` and `try catch` control structures when requesting a storage or any object who can return either "nothing" or the desired result.

The code also becomes more declarative and object oriented.

## Examples

### Using a repository

Context: We want to send a message to an user, only known by his ID.

#### As usually seen

```php
<?php

// ---------------------------------------- interfaces

interface User
{
    public function sendMessage(string $text): void;
}

interface UserRepository
{
    public function userById(int $uuid): User|null;
}

// ---------------------------------------- implementation

$userRepository = new MySqlUserRepository();

$user = $userRepository->userById(1234);

if (true === is_null($user)) {
    // error handling
} else {
    $user->sendMessage('Hi!');
}
```

#### With Result

```php
<?php

// ---------------------------------------- interfaces

interface User
{
    public function sendMessage(string $text): void;
}

interface UserRepository
{
    public function userById(int $uuid): Result<User>;
}

// ---------------------------------------- implementation

$userRepository = new MySqlUserRepository();

$userRepository
    ->userById(1234)
    ->then(function (User $user) {
        $user->sendMessage('Hi!');
    })
    ->else(function (Error $error) {
        // error handling
    });
```

### Within a HTTP controller

Context: We want to fetch an user name and return it using an HTTP#200 response if the user is known, or via an HTTP#404 if the user is unknown.

#### As usually seen

```php
<?php

// ---------------------------------------- interfaces

interface User
{
    public function name(): string;
}

interface UserRepository
{
    public function userById(int $uuid): User|null;
}

// ---------------------------------------- implementation

class UserNameController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(int $uuid): Response {
        $user = $this->userRepository->userById($uuid);

        if (true === is_null($user)) {
            $response = new Response(
                sprintf('the user %s does not exist', $uuid),
                404
            );
        } else {
            $response = new Response($user->name(), 200);
        }

        return $response;
    }
}
```

#### With Result

```php
<?php

// ---------------------------------------- interfaces

interface User
{
    public function name(): string;
}

interface UserRepository
{
    public function userById(int $uuid): Result<User>;
}

// ---------------------------------------- implementation

class UserNameController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(int $uuid): Response {
        return $this->userRepository
            ->userById($uuid)
            ->map(function (User $user) {
                return new Response($user->name(), 200);
            })
            ->getOr(function (Error $error) use ($uuid) {
                return new Response(
                    sprintf('the user %s does not exist', $uuid),
                    404
                );
            });
    }
}
```

### How to manage multiple uncertainties ?

Context: We want to add an user to a group. But only the corresponding IDs are provided. Both may not exist.

#### As usually seen

```php
<?php

// ---------------------------------------- interfaces

interface User
{
    public function name(): string;
}

interface Group
{
    public function add(User $user): void;
}

interface UserRepository
{
    public function userById(int $uuid): User|null;
}

interface GroupRepository
{
    public function groupById(int $uuid): Group|null;
}

// ---------------------------------------- implementation

$group = $this->groupRepository->groupById($groupUuid);
$user  = $this->userRepository->userById($userUuid);

if (true === is_null($user) || true === is_null($user)) {
    // error handling
} else {
    $group->add($user);
}
```

#### With Result

```php
<?php

// ---------------------------------------- interfaces

interface User
{
    public function name(): string;
}

interface Group
{
    public function add(User $user): void;
}

interface UserRepository
{
    public function userById(int $uuid): Result<User>;
}

interface GroupRepository
{
    public function groupById(int $uuid): Result<Group>;
}

// ---------------------------------------- implementation

(new Combined([
    $this->groupRepository->groupById($groupUuid),
    $this->userRepository->userById($userUuid),
]))
    ->then(function (Group $group, User $user) {
        $group->add($user);
    })
    ->else(function (Error $error) {
        /** error handling */
    });
```

or

```php
<?php

// ---------------------------------------- implementation

$this->groupRepository
    ->groupById($groupUuid)
    ->join($this->userRepository->userById($userUuid))
    ->then(function (Group $group, User $user) {
        $group->add($user);
    })
    ->else(function (Error $error) {
        /** error handling */
    });
```
