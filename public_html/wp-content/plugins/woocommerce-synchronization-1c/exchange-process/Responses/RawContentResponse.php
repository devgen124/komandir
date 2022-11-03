<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base\Response;

/**
 * Displaying the `raw` response content for 1C.
 */
class RawContentResponse extends Response
{
    /**
     * @param string $message
     *
     * @return void
     */
    public function send($message)
    {
        $this->clearBuffer();

        echo $message;
        // escape ok
    }
}
