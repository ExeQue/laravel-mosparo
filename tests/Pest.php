<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Mosparo\ApiClient\RequestHelper;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

/**
 * Answers verification requests the way mosparo does, signed with the
 * configured private key, and returns the request bodies it received.
 *
 * @param bool $forged Sign with the wrong key, as an answer that did not come from mosparo.
 * @param list<string>|null $verified The fields mosparo checked; every submitted field when null.
 *
 * @return ArrayObject<int, array<string, mixed>>
 */
function mosparoAnswers(bool $valid = true, bool $forged = false, string $project = 'default', ?array $verified = null): ArrayObject
{
    $requests = new ArrayObject();

    config()->set("mosparo.projects.{$project}.client_arguments.handler", function (RequestInterface $request) use ($valid, $forged, $project, $verified, $requests): PromiseInterface {
        $body = json_decode((string) $request->getBody(), true);
        $requests[] = $body;

        $helper = new RequestHelper('', $forged ? 'someone-else' : config("mosparo.projects.{$project}.private_key"));

        return Create::promiseFor(new Response(200, [], (string) json_encode([
            'valid' => $valid,
            'verificationSignature' => $helper->createHmacHash($body['validationSignature'].$body['formSignature']),
            'verifiedFields' => array_fill_keys($verified ?? array_keys($body['formData']), 'valid'),
            'issues' => [],
        ])));
    });

    return $requests;
}

function mosparoIsUnreachable(string $project = 'default'): void
{
    config()->set("mosparo.projects.{$project}.client_arguments.handler", fn (RequestInterface $request): PromiseInterface => Create::rejectionFor(
        new ConnectException('Connection refused', $request),
    ));
}

/**
 * Answers mosparo's data API by path, and returns the requests it received.
 * A path given a list of bodies answers them in turn, the last one repeating.
 *
 * @param array<string, string|array<mixed>|list<string|array<mixed>>> $routes
 *
 * @return ArrayObject<int, RequestInterface>
 */
function mosparoApi(array $routes, string $project = 'default'): ArrayObject
{
    $requests = new ArrayObject();
    $served = [];

    config()->set("mosparo.projects.{$project}.client_arguments.handler", function (RequestInterface $request) use ($routes, $requests, &$served): PromiseInterface {
        $requests[] = $request;
        $path = $request->getUri()->getPath();
        $bodies = $routes[$path] ?? ['error' => true, 'errorMessage' => "No route for {$path}"];
        $bodies = is_array($bodies) && array_is_list($bodies) ? $bodies : [$bodies];
        $served[$path] = ($served[$path] ?? -1) + 1;
        $body = $bodies[min($served[$path], count($bodies) - 1)];

        return Create::promiseFor(new Response(200, [], is_string($body) ? $body : (string) json_encode($body)));
    });

    return $requests;
}
