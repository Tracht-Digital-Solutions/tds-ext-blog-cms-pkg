<?php
declare(strict_types=1);

/**
 * Minimal stand-in for Phinx's base class.
 *
 * The migrations are plain files in the global namespace that Phinx `require`s
 * at run time; they are not autoloaded and Phinx itself is not a dependency of
 * this package (the composed API host brings it). To let a test read the
 * constants out of a migration class, the parent has to exist — nothing more
 * than that is needed, because no test calls `up()` or `down()`.
 */

namespace Phinx\Migration;

if (!class_exists(AbstractMigration::class, false)) {
    abstract class AbstractMigration
    {
    }
}
