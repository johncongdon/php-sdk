<?php declare(strict_types=1);

namespace JustSteveKing\PhpSdk;

use JustSteveKing\HttpAuth\Strategies\Interfaces\StrategyInterface;
use JustSteveKing\HttpSlim\HttpClient;
use JustSteveKing\PhpSdk\Resources\AbstractResource;
use JustSteveKing\UriBuilder\Uri;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Client
{
    /**
     * @var StrategyInterface
     */
    private StrategyInterface $strategy;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $factory;

    /**
     * @var HttpClient
     */
    private HttpClient $http;

    /**
     * @var Uri
     */
    private Uri $uri;

    public function __construct(ClientBuilder $builder)
    {
        $this->uri = $builder->uri();
        $this->http = $builder->transport();
        $this->factory = $builder->factory();
        $this->strategy = $builder->strategy();
    }

    public function strategy(): StrategyInterface
    {
        return $this->strategy;
    }

    public function addResource(string $name, AbstractResource $resource): self
    {
        $this->factory()->set($name, $resource);

        return $this;
    }

    public function uri(): Uri
    {
        return $this->uri;
    }

    public function factory(): ContainerInterface
    {
        return $this->factory;
    }

    public function __get(string $name)
    {
        if (! $this->factory()->has($name)) {
            throw new RuntimeException("Resource {$name} has not been registered with the SDK.");
        }

        $resource = $this->factory()->get($name);

        $resource->setHttp($this->http)
            ->setUri($this->uri)
            ->setStrategy($this->strategy)
            ->loadPath();

        return $resource;
    }
}
