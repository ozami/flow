<?php

use Coroq\Flow\MapToFileV3;

require_once __DIR__ . "/utils.php";

class MapToFileV3Test extends PHPUnit_Framework_TestCase {
  public function testLoadFileInTheRootDirectory() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/file-only/dir1/dir2", "getPath");
    $params = ["path" => "file.php"];
    $result = $map($params, "asis");
    $this->assertEquals(["out" => "file"] + $params, $result);
  }

  public function testLoadFileInSubDirectory() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/file-only", "getPath");
    $params = ["path" => "/dir1/dir2/file.php"];
    $result = $map($params, "asis");
    $this->assertEquals($params + ["out" => "file"], $result);
  }

  public function testLoadHookFromJustOneLevelDeep() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/has-dir-hooks/dir1/dir2", "getPath");
    $params = ["path" => "/file.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals([
      "out" => [
        "dir2-all-begin",
        "dir2-dir-begin",
        "file",
        "dir2-dir-end",
        "dir2-all-end",
      ],
    ] + $params, $result);
  }

  public function testLoadDirectoryHooksFromDeeplyNestedDirectories() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/has-dir-hooks", "getPath");
    $params = ["path" => "/dir1/dir2/file.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals([
      "out" => [
        "root-all-begin",
        "dir1-all-begin",
        "dir2-all-begin",
        "dir2-dir-begin",
        "file",
        "dir2-dir-end",
        "dir2-all-end",
        "dir1-all-end",
        "root-all-end",
      ],
    ] + $params, $result);
  }

  public function testLoadFileThatReturnsNull() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/has-dir-hooks", "getPath");
    $params = ["path" => "/dir1/dir2/null.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals([
      "out" => [
        "root-all-begin",
        "dir1-all-begin",
        "dir2-all-begin",
        "dir2-dir-begin",
        "dir2-dir-end",
        "dir2-all-end",
        "dir1-all-end",
        "root-all-end",
      ],
    ] + $params, $result);
  }

  /**
   * @expectedException LogicException
   */
  public function testRootDirectoryDoesNotExist() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/not-exist", "getPath");
  }

  public function testIntermediateDirectoryDoesNotExist() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/has-dir-hooks", "getPath");
    $params = ["path" => "/dir1/not-exist/file.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals([
      "out" => [
        "root-all-begin",
        "dir1-all-begin",
        "dir1-all-end",
        "root-all-end",
      ],
    ] + $params, $result);
  }

  public function testFuncFileDoesNotExist() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/has-dir-hooks", "getPath");
    $params = ["path" => "/dir1/dir2/not-exist.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals([
      "out" => [
        "root-all-begin",
        "dir1-all-begin",
        "dir2-all-begin",
        "dir2-dir-begin",
        "dir2-dir-end",
        "dir2-all-end",
        "dir1-all-end",
        "root-all-end",
      ],
    ] + $params, $result);
  }

  public function testFuncFileInRootDoesNotExist() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/has-dir-hooks", "getPath");
    $params = ["path" => "/not-exist.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals([
      "out" => [
        "root-all-begin",
        "root-dir-begin",
        "root-dir-end",
        "root-all-end",
      ],
    ] + $params, $result);
  }

  public function testRequestForSpecialFileNamesAreIgnored() {
    $map = new MapToFileV3(__DIR__ . "/MapToFileV3/has-dir-hooks", "getPath");
    $params = ["path" => "/__dir__.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals($params, $result);

    $params = ["path" => "/dir1/__dir__.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals($params, $result);

    $params = ["path" => "/__all__.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals($params, $result);

    $params = ["path" => "/dir1/__all__.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals($params, $result);
  }
}
