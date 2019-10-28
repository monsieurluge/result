# Result

The goal of the Result library is to say goodbye to the `if` and `try catch` control structures when requesting a storage or any object who can return either "nothing" or the desired result.

The code also becomes more declarative and object oriented.

## Objects types

### Error

The Error object helps to identify the error thrown in order to trace it efficiently.

Example, using an `EmployeeNotFound` error thrown when the employee was not found in the corresponding storage:

```php
<?php

namespace App\Error\Storage;

use App\Domain\Employee\UniqueId;
use monsieurluge\Result\Error\Error;

final class EmployeeNotFound implements Error
{
    private $identifier;

    public function __construct(UniqueId $identifier)
    {
        $this->identifier = $identifier;
    }

    public function code(): string
    {
        return 'emp-42'; // a dedicated and unique error code
    }

    public function message(): string
    {
        return sprintf(
            'the employee identified by "%s" does not exist',
            $this->identifier->toString()
        );
    }
}
```

```php
<?php

namespace App\Repository\Doctrine; // or any storage type needed

use App\Domain\Employee\UniqueId;
use App\Repository\UserRepository;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Failure;
use monsieurluge\Result\Result\Success;

final class SqlUserRepository implements UserRepository
{
    // property declarations, constructor, etc

    public function user(UniqueId $identifier): Result // a Result<User>
    {
        $row = $this->storage->getUserById($identifier->toString());

        return is_null($row)
            ? new Failure(new UserNotFound($identifier))
            : new Success($this->userFactory->fromRawSqlData($row));
    }
}
```

### Result

The Result objects help to write declarative code and to throw away the usuals `if (is_null($object))` and `try catch` control structures.

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
            ->then(new PresentProduct($newSuperProduct)) // Result<Person>
            ->then(new Sale($newSuperProduct)) // Result<Person>
            ->map(function (Person $client) use ($newSuperProduct) { // Result<Bill>
                return $this->createBill($client, $newSuperProduct);
            })
            ->else(new SaleRefused($this)); // Failure
    }
}
```

## Complete examples

### Send a HTTP #200 or #404 response via a controller

```php
<?php

namespace App\Domain;

use App\Services\Output\Output;

interface Product
{
    public function print(Output $output): void;
}
```

```php
<?php

namespace App\Repository;

use monsieurluge\Result\Result\Result;
use App\Domain\Product as DomainProduct;

interface Product
{
    public function productById(int $identifier): Result; // Result<DomainProduct>
}
```

```php
<?php

namespace App\Http\Controller\API;

use monsieurluge\Result\Error\BaseError;
use monsieurluge\Result\Error\Error;
use monsieurluge\Result\Result\Result;
use monsieurluge\Result\Result\Success;
use App\Domain\User;
use App\Repository\Product as ProductRepository;
use Symfony\Component\HttpFoundation\Response;

class ProductCardInformations
{
    private $repository;
    private $view;

    public function __construct(ProductRepository $repository, ProductCard $view)
    {
        $this->repository = $repository;
        $this->view       = $view;
    }

    public function process(Request $request, Response $response): void
    {
        $this->repository
            ->productById($request->productId())
            ->then(function (Product $product) { $product->print($this->view); })

            // todo
    }
}
```
