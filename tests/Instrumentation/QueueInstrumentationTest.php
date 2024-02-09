<?php

use Illuminate\Support\Facades\Artisan;
use Keepsuit\LaravelOpenTelemetry\Facades\Tracer;
use Keepsuit\LaravelOpenTelemetry\Tests\Support\TestJob;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use Spatie\Valuestore\Valuestore;

beforeEach(function () {
    $this->valuestore = Valuestore::make(__DIR__.'/testJob.json')->flush();
});

afterEach(function () {
    $this->valuestore->flush();
});

it('can trace queue jobs', function () {
    $spanId = '';
    $traceId = '';

    Tracer::newSpan('dispatcher')
        ->setSpanKind(SpanKind::KIND_PRODUCER)
        ->measure(function (SpanInterface $span) use (&$traceId, &$spanId) {
            $spanId = $span->getContext()->getSpanId();
            $traceId = $span->getContext()->getTraceId();

            return dispatch(new TestJob($this->valuestore));
        });

    expect($traceId)
        ->not->toBeEmpty()
        ->not->toBe('00000000000000000000000000000000');

    expect($spanId)
        ->not->toBeEmpty()
        ->not->toBe('0000000000000000');

    Artisan::call('queue:work', [
        '--once' => true,
    ]);

    expect($this->valuestore)
        ->get('traceparentInJob')->toBe(sprintf('00-%s-%s-01', $traceId, $spanId))
        ->get('traceIdInJob')->toBe($traceId);
});
