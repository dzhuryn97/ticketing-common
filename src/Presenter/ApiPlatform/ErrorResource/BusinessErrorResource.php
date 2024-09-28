<?php

namespace Ticketing\Common\Presenter\ApiPlatform\ErrorResource;

use ApiPlatform\Metadata\Error as Operation;
use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\WebLink\Link;
use Ticketing\Common\Domain\Exception\BusinessException;

#[ErrorResource(
    uriTemplate: '/business_errors/{status}',
    types: ['BusinessError'],
    operations: [
        new Operation(
            name: '_api_errors_problem',
            routeName: 'api_errors',
            outputFormats: ['json' => ['application/problem+json']],
            normalizationContext: [
                'groups' => ['jsonproblem'],
                'skip_null_values' => true,
                'rfc_7807_compliant_errors' => true,
            ],
        ),
        new Operation(
            name: '_api_errors_hydra',
            routeName: 'api_errors',
            outputFormats: ['jsonld' => ['application/problem+json']],
            normalizationContext: [
                'groups' => ['jsonld'],
                'skip_null_values' => true,
                'rfc_7807_compliant_errors' => true,
            ],
            links: [new Link(rel: 'http://www.w3.org/ns/json-ld#error', href: 'http://www.w3.org/ns/hydra/error')],
        ),
        new Operation(
            name: '_api_errors_jsonapi',
            routeName: 'api_errors',
            outputFormats: ['jsonapi' => ['application/vnd.api+json']],
            normalizationContext: [
                'groups' => ['jsonapi'],
                'skip_null_values' => true,
                'rfc_7807_compliant_errors' => true,
            ],
        ),
        new Operation(
            name: '_api_errors',
            routeName: 'api_errors'
        ),
    ],
    openapi: false,
    graphQlOperations: [],
    provider: 'api_platform.state.error_provider'
)]
class BusinessErrorResource implements ProblemExceptionInterface
{
    public function __construct(
        private string $title,
        private string $message,
        private string $type,
        private int $status,
    ) {
    }

    public static function createFromException(BusinessException $exception, int $status)
    {
        return new self('An error occurred', $exception->getMessage(), $exception->getType(), $status);
    }

    #[Groups(['jsonld'])]
    public function getType(): string
    {
        return $this->type;
    }

    #[Groups(['jsonld'])]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    #[Groups(['jsonld'])]
    public function getStatus(): ?int
    {
        return $this->status;
    }

    #[Groups(['jsonld'])]
    public function getDetail(): ?string
    {
        return $this->message;
    }

    public function getInstance(): ?string
    {
        return null;
    }
}
