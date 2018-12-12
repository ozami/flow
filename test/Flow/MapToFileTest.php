<?php

use Coroq\Flow\MapToFile;

require_once __DIR__ . "/utils.php";

class MapToFileTest extends PHPUnit_Framework_TestCase {
  public function testLoadFileInTheRootDirectory() {
    $map = new MapToFile(__DIR__ . "/MapToFile/file-only/dir1/dir2", "getPath");
    $params = ["path" => "file.php"];
    $result = $map($params, "asis");
    $this->assertEquals(["out" => "file"] + $params, $result);
  }

  public function testLoadFileInSubDirectory() {
    $map = new MapToFile(__DIR__ . "/MapToFile/file-only", "getPath");
    $params = ["path" => "/dir1/dir2/file.php"];
    $result = $map($params, "asis");
    $this->assertEquals($params + ["out" => "file"], $result);
  }

  public function testLoadHookFromJustOneLevelDeep() {
    $map = new MapToFile(__DIR__ . "/MapToFile/has-dir-hooks/dir1/dir2", "getPath");
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
    $map = new MapToFile(__DIR__ . "/MapToFile/has-dir-hooks", "getPath");
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

  public function testEarlyReturn() {
    $map = new MapToFile(__DIR__ . "/MapToFile/has-dir-hooks", "getPath");
    $params = ["path" => "/early-return/file.php", "out" => []];
    $result = $map($params, "asis");
    $this->assertEquals([
      "out" => [
        "root-all-begin",
        "early-return-dir",
        "root-all-end",
      ],
    ] + $params, $result);
  }

  /**
   * @expectedException LogicException
   */
  public function testRootDirectoryDoesNotExist() {
    $map = new MapToFile(__DIR__ . "/MapToFile/not-exist", "getPath");
  }

  public function testIntermediateDirectoryDoesNotExist() {
    $map = new MapToFile(__DIR__ . "/MapToFile/has-dir-hooks", "getPath");
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
    $map = new MapToFile(__DIR__ . "/MapToFile/has-dir-hooks", "getPath");
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
    $map = new MapToFile(__DIR__ . "/MapToFile/has-dir-hooks", "getPath");
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
    $map = new MapToFile(__DIR__ . "/MapToFile/has-dir-hooks", "getPath");
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

  /**
   * @expectedException DomainException
   */
  public function testLoadFunctionFromFileThrowsExceptionWhenTheFileDidNotReturnCallable() {
    $map = new MapToFile(__DIR__ . "/MapToFile/", "getPath");
    $map->loadFunctionFromFile(__DIR__ . "/MapToFile/return_nothing.php");
  }

  public function testResolveDots() {
    $map = new MapToFile(__DIR__ . "/MapToFile/", "getPath");
    $this->assertSame(
      $map->resolveDots("//test1//test2//test3///..//"),
      "test1/test2"
    );
    $this->assertSame(
      $map->resolveDots("././test1/.test2/././"),
      "test1/.test2"
    );
    $this->assertSame(
      $map->resolveDots("../test1/..test2/test3/.."),
      "test1/..test2"
    );
    $this->assertSame(
      $map->resolveDots(".//..//test1//test2/./..//.//test3//"),
      "test1/test3"
    );
  }
}
