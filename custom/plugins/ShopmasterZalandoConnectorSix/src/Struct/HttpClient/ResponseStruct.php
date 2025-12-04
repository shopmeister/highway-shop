<?php

namespace ShopmasterZalandoConnectorSix\Struct\HttpClient;


use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\ErrorException;
use ShopmasterZalandoConnectorSix\Struct\Struct;

class ResponseStruct extends Struct implements \IteratorAggregate, \ArrayAccess
{
    /**
     * @var int
     */
    protected int $status = 0;
    /**
     * @var string
     */
    protected string $content = '';
    /**
     * @var array|null
     */
    protected ?array $contentArray = null;
    /**
     * @var RequestStruct|null
     */
    private ?RequestStruct $request;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
        $array = json_decode($content, 1);
        if (is_array($array)) {
            $this->contentArray = $array;
        }

    }

    /**
     * @return array|null
     */
    public function getContentArray(): ?array
    {
        return $this->contentArray;
    }

    /**
     * @return RequestStruct|null
     */
    public function getRequest(): ?RequestStruct
    {
        return $this->request;
    }

    /**
     * @param RequestStruct|null $request
     * @return ResponseStruct
     */
    public function setRequest(?RequestStruct $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->contentArray[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->contentArray[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset): void
    {
        // TODO: Implement offsetUnset() method.
    }

    public function getIterator(): \Traversable
    {
        yield from $this->contentArray;
    }

    /**
     * @param bool $throwException
     * @return bool
     * @throws ErrorException
     */
    public function isSuccessStatus(bool $throwException = false): bool
    {
        $success = ($this->getStatus() >= 200 && $this->getStatus() < 300);
        if ($throwException && !$success) {
            throw new ErrorException($this->getContent(), $this->getStatus());
        }
        return $success;
    }

    public function __toString(): string
    {
        return $this->getContent();
    }

    /**
     * @param mixed $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->contentArray[$key] ?? null;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->contentArray;
    }

}