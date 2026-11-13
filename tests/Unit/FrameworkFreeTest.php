<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\Slugger;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * The library runs with no WordPress code loaded, through Composer or
 * through bootstrap.php alone.
 */
final class FrameworkFreeTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLibraryNeedsNoWordPress(): void
    {
        self::assertFalse(function_exists('add_filter'));
        self::assertFalse(function_exists('apply_filters'));
        self::assertSame('namaste-bharat', (new Slugger())->slug('नमस्ते भारत')->slug);
        self::assertFalse(function_exists('add_filter'));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testBootstrapAutoloadsTheLibrary(): void
    {
        require dirname(__DIR__, 2) . '/bootstrap.php';
        self::assertTrue(class_exists(Slugger::class));
        self::assertSame('moskva', (new Slugger())->slug('Москва')->slug);
    }
}
