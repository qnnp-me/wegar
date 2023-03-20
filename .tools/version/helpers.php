<?php
include './../../vendor/autoload.php';

use PHLAK\SemVer\Exceptions\InvalidVersionException;
use PHLAK\SemVer\Version;

/**
 * @throws InvalidVersionException
 * @throws Exception
 */
function set_new_version(
    bool   $major = false,
    bool   $minor = false,
    bool   $patch = false,
    string $prerelease = null,
): void
{
    $current_version = exec("git for-each-ref --sort=creatordate --format '%(tag)' refs/tags");

    $add_version = ($major ? 'Major' : ($minor ? 'Minor' : ($patch ? 'Patch' : false)));

    !$add_version && throw new Exception('请选择升级版本 Major/Minor/Patch');

    try {
        $version = new Version($current_version);
    } catch (InvalidVersionException $e) {
        $version = new Version('0.0.0');
    }
    $current_version = clone $version;

    $build = preg_match("/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)/", $version->build ?? '') ? $version->build : '0.0.0';
    if (
        $version->preRelease &&
        (
            ($minor && $version->patch > 0)
            || ($major && ($version->patch ?: $version->minor) > 0)
            || ($patch && $version->patch == 0)
            || ($minor && $version->minor == 0)
        )
    ) {
        echo "请按照顺序发布版本，当前版本：$version\n";
        return;
    }
    if ($prerelease) {
        if (!$version->preRelease) {
            $version->{"increment$add_version"}();
        }
        if (!preg_match("/^$prerelease/", $version->preRelease ?? '')) {
            $version->setPreRelease($prerelease);
        } else {
            $version->incrementPreRelease();
        }
        if ($version->lt($current_version)) {
            die("目标版本 $prerelease 已结束，请进行下一版本发布");
        }
    } else {
        if ($version->preRelease)
            $version->setPreRelease('');
        else
            $version->{"increment$add_version"}();
    }
    build_add($build, $add_version);
    $version->setBuild((string)$build);
    $version = $version->prefix();


    $log_end = (string)$current_version === '0.0.0' ? '' : "...v$current_version";
    $change_logs = shell_exec("git log --pretty=format:'%s[LOG]' HEAD$log_end") ?? $version;
    $change_logs = explode("[LOG]", $change_logs);
    $change_logs = array_map(function ($change_log) {
        $change_log = trim($change_log);
        if (preg_match("/新增.*功能/i", $change_log)) {
            $change_log = "⭐ $change_log";
        } elseif (preg_match("/修复.*缺陷/i", $change_log)) {
            $change_log = "🐞 $change_log";
        } elseif (preg_match("/优化.+/i", $change_log)) {
            $change_log = "🔨 $change_log";
        } elseif (preg_match("/依赖项/i", $change_log)) {
            $change_log = "🖇 $change_log";
        } elseif (preg_match("/^@/i", $change_log)) {
            $change_log = "🔨 内部优化";
        } else {
            $change_log = "⚪ $change_log";
        }
        return $change_log;
    }, $change_logs);
    array_pop($change_logs);
    $change_logs = "$version\n----------\nChange Log  :\n" . implode("\n", array_unique($change_logs));
    echo "Version change: v$current_version -> $version\n";
    echo "{$change_logs}\n\n";

    echo "确认升级到: $version ？输入 yes 确认: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'yes') {
        echo "已取消!\n";
        fclose($handle);
        return;
    }
    fclose($handle);

    echo shell_exec("git push origin dev");
    echo shell_exec("git push origin dev:main");
    echo "创建 Tag {$version} ...";
    echo shell_exec("git tag -a $version -m '$change_logs'") ?? "Done\n";
    echo shell_exec("git push origin dev --tags ");
    echo shell_exec("git push origin dev:main --tags ");
}

function build_add(&$build, $add_version): void
{
    $build = explode('.', $build);
    $build[array_search($add_version, ['Major', 'Minor', 'Patch'])] += 1;
    $build = implode('.', $build);
}
