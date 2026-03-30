<?php

interface DeployableInterface
{
  public function deploy(): string;
  public function terminate(): string;
}

trait HealthCheckTrait
{
  public function performCheck(): string
  {
    return "System is checking resource state...\n";
  }
}

class CloudResource
{
  public string $providerName;
  protected string $currentStatus = "idle";
  private string $apiKey;

  public function __construct(string $providerName, string $apiKey)
  {
    $this->providerName = $providerName;
    $this->apiKey = $apiKey;
  }

  public function getMaskedKey(): string
  {
    return substr($this->apiKey, 0, 4) . "****";
  }

  public function setApiKey(string $newKey): void
  {
    $this->apiKey = $newKey;
  }
}

class ContainerInstance extends CloudResource implements DeployableInterface
{
  use HealthCheckTrait;

  public string $imageName;

  public function __construct(
    string $providerName,
    string $apiKey,
    string $imageName,
  ) {
    parent::__construct($providerName, $apiKey);
    $this->imageName = $imageName;
  }

  public function deploy(): string
  {
    $this->currentStatus = "active";
    return $this->performCheck() .
      "Container with image {$this->imageName} started!";
  }

  public function terminate(): string
  {
    $this->currentStatus = "terminated";
    return "Container with image {$this->imageName} stopped!";
  }

  public function getStatus(): string
  {
    return $this->currentStatus;
  }
}

$myContainer = new ContainerInstance("DockerHub", "AX12-99-BF", "nginx:latest");

$output = "Masked container key: " . $myContainer->getMaskedKey() . "\n";
$output .= $myContainer->deploy() . "\n";
$output .= "Container status: " . $myContainer->getStatus() . "\n";
$myContainer->setApiKey("BF24-82-YX");
$output .= "Changed api key: " . $myContainer->getMaskedKey();

echo nl2br($output);

?>
