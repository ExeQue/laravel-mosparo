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
 *
 * @return ArrayObject<int, array<string, mixed>>
 */
function mosparoAnswers(bool $valid = true, bool $forged = false, string $project = 'default'): ArrayObject
{
    $requests = new ArrayObject();

    config()->set("mosparo.projects.{$project}.client_arguments.handler", function (RequestInterface $request) use ($valid, $forged, $project, $requests): PromiseInterface {
        $body = json_decode((string) $request->getBody(), true);
        $requests[] = $body;

        $helper = new RequestHelper('', $forged ? 'someone-else' : config("mosparo.projects.{$project}.private_key"));

        return Create::promiseFor(new Response(200, [], (string) json_encode([
            'valid' => $valid,
            'verificationSignature' => $helper->createHmacHash($body['validationSignature'].$body['formSignature']),
            'verifiedFields' => [],
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
