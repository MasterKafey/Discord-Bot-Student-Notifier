<?php

namespace App\Business;

use Symfony\Component\Yaml\Yaml;

readonly class ConfigBusiness
{
    public function __construct(
        private readonly string $configFilePath,
    )
    {

    }

    public function get($key): mixed
    {
        return array_merge($this->getDefaults(), $this->getFileContent())[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        if (!array_key_exists($key, $this->getDefaults())) {
            throw new \Exception("Invalid configuration key '$key'");
        }

        $this->setFileContent(array_merge($this->getFileContent(), [$key => $value]));
    }

    private function getFileContent(): array
    {
        if (!file_exists($this->configFilePath)) {
            $this->setFileContent([
                'student_role' => '~'
            ]);
        }

        return Yaml::parseFile($this->configFilePath);
    }

    private function setFileContent(array $data): void
    {
        file_put_contents($this->configFilePath, Yaml::dump($data));
    }

    public function getDefaults(): array
    {
        return [
            'student_role' => null,
            'output_channel' => null,
            'inactivity_duration_day' => 3,
            'interval_notification_day' => 3,
            'notifications_number_before_email' => 1,
            'category_student_channels' => null,
            'email_subject' => null,
            'email_text' => null,
            'automatic_emails' => false,
        ];
    }
}