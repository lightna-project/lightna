<?php

declare(strict_types=1);

namespace Lightna\Newrelic\App;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\Data\Request;

class Newrelic extends ObjectA
{
    /** @AppConfig(newrelic/transaction/name_mapping) */
    protected array $transactionNameMapping;
    protected Request $request;

    protected string $transactionNamePrefix = 'lightna/';

    protected function init(array $data = []): void
    {
        $this->stubFunctions();
    }

    public function setTransactionName(string $name): void
    {
        newrelic_name_transaction($this->getFullTransactionName($name));
    }

    public function registerAction(array $action): void
    {
        $this->setTransactionName($this->actionToName($action));
    }

    public function registerRedirect(): void
    {
        $this->setTransactionName('redirect');
    }

    public function registerNoRoute(): void
    {
        $this->setTransactionName('page/no-route');
    }

    protected function getFullTransactionName(string $name): string
    {
        $fullName = $this->transactionNamePrefix . $name;

        return $this->transactionNameMapping[$fullName] ?? $fullName;
    }

    protected function actionToName(array $action): string
    {
        if ($this->request->uriPath === '') {
            return 'page/home';
        }

        if ($action['name'] === 'page') {
            $name = $action['name'] . '/' . $action['params']['type'];
        } else {
            $name = 'action/' . $action['name'];
        }

        return $name;
    }

    protected function stubFunctions(): void
    {
        if (extension_loaded('newrelic')) {
            return;
        }

        function newrelic_name_transaction(string $name): bool
        {
            return true;
        }
    }
}
