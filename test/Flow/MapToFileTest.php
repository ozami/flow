<?php

use Coroq\Flow\MapToFile;

class MapToFileTest extends PHPUnit_Framework_TestCase {
  public function testLoadFileInTheRootDirectory() {
    $map = new MapToFile("path", __DIR__ . "/MapToFile/file-only/dir1/dir2");
    $params = ["path" => "file.php"];
    $result = $map($params);
    $this->assertEquals(["out" => "file"] + $params, $result);
  }
  
  public function testLoadFileInSubDirectory() {
    $map = new MapToFile("path", __DIR__ . "/MapToFile/file-only");
    $params = ["path" => "/dir1/dir2/file.php"];
    $result = $map($params);
    $this->assertEquals($params + ["out" => "file"], $result);
  }
  
  public function testLoadDirectoryHookFromJustOneLevelDeep() {
    $map = new MapToFile("path", __DIR__ . "/MapToFile/has-dir-hooks/dir1/dir2");
    $params = ["path" => "/file.php", "out" => []];
    $result = $map($params);
    $this->assertEquals([
      "out" => [
        "dir2-before(1)",
        "file",
        "dir2-after",
      ],
    ] + $params, $result);
  }
  
  public function testLoadDirectoryHooksFromDeeplyNestedDirectories() {
    $map = new MapToFile("path", __DIR__ . "/MapToFile/has-dir-hooks");
    $params = ["path" => "/dir1/dir2/file.php", "out" => []];
    $result = $map($params);
    $this->assertEquals([
      "out" => [
        "root-before()",
        "dir1-before()",
        "dir2-before(1)",
        "file",
        "dir2-after",
        "dir1-after",
        "root-after",
      ],
    ] + $params, $result);
  }

  /**
   * @expectedException LogicException
   */
  public function testRootDirectoryDoesNotExist() {
    $map = new MapToFile("path", __DIR__ . "/MapToFile/not-exist");
  }
}
