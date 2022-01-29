<?php

namespace Keepsuit\LaravelOpenTelemetry\Grpc\Interceptors;

use Grpc\Interceptor;
use Keepsuit\LaravelOpenTelemetry\Facades\Tracer;

class ParentSpanTraceInterceptor extends Interceptor
{
    public function interceptUnaryUnary($method, $argument, $deserialize, $continuation, array $metadata = [], array $options = [])
    {
        $metadata = $this->injectParentSpanTrace($metadata);

        return $continuation($method, $argument, $deserialize, $metadata, $options);
    }

    public function interceptStreamUnary($method, $deserialize, $continuation, array $metadata = [], array $options = [])
    {
        $metadata = $this->injectParentSpanTrace($metadata);

        return $continuation($method, $deserialize, $metadata, $options);
    }

    public function interceptUnaryStream($method, $argument, $deserialize, $continuation, array $metadata = [], array $options = [])
    {
        $metadata = $this->injectParentSpanTrace($metadata);

        return $continuation($method, $argument, $deserialize, $metadata, $options);
    }

    public function interceptStreamStream($method, $deserialize, $continuation, array $metadata = [], array $options = [])
    {
        $metadata = $this->injectParentSpanTrace($metadata);

        return $continuation($method, $deserialize, $metadata, $options);
    }

    protected function injectParentSpanTrace(array $metadata): array
    {
        $metadata = array_merge($metadata, Tracer::activeSpanB3Headers());

        return $metadata;
    }
}