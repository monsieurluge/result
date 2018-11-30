# Result

The goal of the Result library is to say goodbye to the `if` and `try catch` control structures when requesting a storage or any object who can return either "nothing" or the desired result.

The code also becomes more declarative and object oriented.

## Objects types

### Action

The Action objects allows to interact with the desired result's content without changing its type.

Example, using a `SendEmail` action based on a `Result<User>`:

```php
<?php

namespace App\Action;

use monsieurluge\Result\Action\Action;

final class SendEmail implements Action
{
    private $mailer; // mail service
    private $template; // template builder

    [...] // constructor

    public function process($target): void
    {
        $mailer->send(
            [ $target->email()->value() ],
            $template->content([ 'name' => $target->fullName() ])
        );
    }
}
```

### Error

The Error object helps to identify the error thrown in order to trace it efficiently.

Example, using a `UserNotFound` error thrown when the user was not found in the storage:

```php
<?php

namespace App\Error;

use App\Domain\UserId;
use monsieurluge\Result\Error\Error;

final class UserNotFound implements Error
{
    private $uniqueId;

    public function __construct(UserId $uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    public function code(): string
    {
        return 'sto-42'; // a dedicated and unique error code
    }

    public function message(): string
    {
        return sprintf(
            'the user "%s" does not exist',
            $this->uniqueId->value()
        );
    }
}
```

```php
<?php

namespace App\Repository;

use App\Domain\UserId;
use App\Repository\UserRepository;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Success;

final class BaseUserRepository implements UserRepository
{
    [...] // variables declarations, constructor, etc

    public function user(UserId $name): Result // Result<User>
    {
        $user = $this->storage->getUserByName($name->value());

        return is_null($user)
            ? new Failure(
                new UserNotFound($name)
            )
            : new Success($this->userFactory->fromDbModel($user));
    }
}
```

### Result

The Result objects helps to write declarative code and to throw away the usuals `if (is_null($object))` and `try catch` control structures.

So, the Exceptions are only used for what they are originally intended for: throw an alert because of a exceptional situation that cannot be handled normally.

Example, using a complete selling process:

```php
<?php

final class Agent
{
    [...] // variables declarations, constructor, etc

    public function sellProduct(Product $newSuperProduct) // Result<Bill>
    {
        return $this->callCenter
            ->call(new PhoneNumber('0123456789')) // Result<Person>
            ->then(new PresentProduct($newSuperProduct))
            ->then(new Sale($newSuperProduct))
            ->map(function (Person $client) use ($newSuperProduct) {
                return $this->createBill($client, $newSuperProduct); // Bill
            })
            ->else(new SaleRefused($this));
    }
}
```

## Complete examples

### Send a HTTP #200 or #404 response via a controller

```php
<?php

use monsieurluge\Result\Action\CustomAction;
use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;
use App\Domain\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

interface User
{
    public function id(): string;

    public function fullname(): string;
}

class UserController
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $userToResponse = function(User $user) { // f(User):Response
            return new Success(
                new Response(
                    [ 'id' => $user->id(), 'name' => $user->fullname() ],
                    Response::HTTP_OK
                )
            );
        };

        $createUserNotFoundResponse = function(Error $error) {
            return new Response(
                sprintf('user not found (error #%s)', $error->code()),
                Response::HTTP_NOT_FOUND
            );
        };

        return $userRepository
            ->findByName('Homer Simpson')
            ->map($userToResponse)
            ->getValueOrExecOnFailure($createUserNotFoundResponse);
    }
}
```
