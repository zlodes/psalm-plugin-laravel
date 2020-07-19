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

      """

    Scenario: base path can be resolved
      Given I have the following code
      """
      require_once base_path('routes/console.php');
      """
      When I run Psalm
      Then I see these errors
        | MissingFile | Cannot find file |

    Scenario: basePath can be resolved from application instance
      Given I have the following code
      """
      require_once app()->basePath('routes/console.php');
      """
      When I run Psalm
      Then I see these errors
        | MissingFile | Cannot find file |
