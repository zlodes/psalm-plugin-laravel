Feature: path helpers
  The global path helpers will return the correct path

  Background:
    Given I have the following config
      """
      <?xml version="1.0"?>
      <psalm totallyTyped="true">
        <projectFiles>
          <directory name="."/>
          <ignoreFiles> <directory name="../../vendor"/> </ignoreFiles>
        </projectFiles>
        <plugins>
          <pluginClass class="Psalm\LaravelPlugin\Plugin"/>
        </plugins>
      </psalm>
      """
    And I have the following code preamble
      """
      <?php declare(strict_types=1);

      namespace Tests\Psalm\LaravelPlugin\Sandbox;
      """

    Scenario: base path can be resolved
      Given I have the following code
      """
      require base_path('routes/console.php');
      """
      When I run Psalm
      Then I see no errors
