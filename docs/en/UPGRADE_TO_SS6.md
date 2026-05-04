# Upgrade Guide: Silverstripe 6

## Framework Requirement

⚠️ **BREAKING CHANGE**: Update `composer.json` to require `silverstripe/framework: ^6.0` (was `~5.0`)

## PHP Version & Syntax

- Add `declare(strict_types=1);` where appropriate (see PruneEmailsTask.php:121)
- Add `#[Override]` attributes to all overridden methods for better IDE support and type safety

## Namespace Changes

⚠️ **BREAKING CHANGE**: `SilverStripe\View\ArrayData` has moved to `SilverStripe\Model\ArrayData`

Update all imports:
```php
// Old
use SilverStripe\View\ArrayData;

// New
use SilverStripe\Model\ArrayData;
```

**Files affected in this module**: `src/Form/ViewEmailButton.php:61-64`

## Build Tasks → Console Commands

⚠️ **BREAKING CHANGE**: BuildTasks now use Symfony Console architecture with PolyExecution

🚨 **CRITICAL REVIEW REQUIRED**: The PruneEmailsTask has been completely rewritten. Key changes:

### Migration Pattern (PruneEmailsTask.php:139-177)

**Old Pattern (SS5)**:
```php
public function run($request)
{
    if (Permission::check('ADMIN')) {
        // logic with $_GET['confirm']
    }
}
```

**New Pattern (SS6)**:
```php
protected static string $commandName = 'prune-emails';
protected string $title = 'Prune old captured emails';
protected static string $description = 'Deletes captured emails older than 1 month';

protected function execute(InputInterface $input, PolyOutput $output): int
{
    // Return Command::SUCCESS or Command::FAILURE
}

public function getOptions(): array
{
    return [
        new InputOption('confirm', 'c', InputOption::VALUE_NONE, 'Confirm deletion'),
    ];
}
```

**Required imports**:
- `use SilverStripe\PolyExecution\PolyOutput;`
- `use Symfony\Component\Console\Command\Command;`
- `use Symfony\Component\Console\Input\InputInterface;`
- `use Symfony\Component\Console\Input\InputOption;`

**Execution changes**:
- Old: `dev/tasks/YourTask?confirm=1`
- New: `sake prune-emails --confirm` or `sake prune-emails -c`

**🚨 RISKY**: If you have custom BuildTasks, they ALL need this migration pattern. Test thoroughly in dev environment.

## Method Signature Changes

### Controller Methods

Ensure controller methods that may return null explicitly return `null` (see CapturedEmailController.php:23). This improves PHP 8+ compatibility.

## Permission Methods

⚠️ **BREAKING CHANGE**: All permission methods (`canView`, `canEdit`, `canDelete`, `canCreate`) now require the `#[Override]` attribute when overriding DataObject defaults.

Apply to:
- `canView($member = null)`
- `canEdit($member = null)` 
- `canDelete($member = null)`
- `canCreate($member = null, $context = [])`

See `src/Model/CapturedEmail.php:84-108` for reference implementation.

## ModelAdmin Changes

Add `#[Override]` attributes to:
- `init()` method (MailCaptureAdmin.php:42)
- `getEditForm($id = null, $fields = null)` method (MailCaptureAdmin.php:49)

## Testing Requirements

**🚨 CRITICAL REVIEW REQUIRED**: Before deploying to production:

1. **Test all BuildTasks** - The execution interface has completely changed
2. **Verify ArrayData imports** - Runtime errors will occur if old namespace is used  
3. **Check permission methods** - Ensure `#[Override]` attributes don't conflict with custom extensions
4. **Test console commands** - Confirm all flags and options work as expected (`--confirm`, `-c`, etc.)

## PHP Version Support

Silverstripe 6 requires **PHP 8.1+**. Verify your environment meets this requirement before upgrading.
