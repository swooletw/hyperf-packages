<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Prompts\Concerns;

use InvalidArgumentException;
use SwooleTW\Hyperf\Prompts\Clear;
use SwooleTW\Hyperf\Prompts\ConfirmPrompt;
use SwooleTW\Hyperf\Prompts\MultiSearchPrompt;
use SwooleTW\Hyperf\Prompts\MultiSelectPrompt;
use SwooleTW\Hyperf\Prompts\Note;
use SwooleTW\Hyperf\Prompts\PasswordPrompt;
use SwooleTW\Hyperf\Prompts\PausePrompt;
use SwooleTW\Hyperf\Prompts\Progress;
use SwooleTW\Hyperf\Prompts\SearchPrompt;
use SwooleTW\Hyperf\Prompts\SelectPrompt;
use SwooleTW\Hyperf\Prompts\Spinner;
use SwooleTW\Hyperf\Prompts\SuggestPrompt;
use SwooleTW\Hyperf\Prompts\Table;
use SwooleTW\Hyperf\Prompts\TextareaPrompt;
use SwooleTW\Hyperf\Prompts\TextPrompt;
use SwooleTW\Hyperf\Prompts\Themes\Default\ClearRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\ConfirmPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\MultiSearchPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\MultiSelectPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\NoteRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\PasswordPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\PausePromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\ProgressRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\SearchPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\SelectPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\SpinnerRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\SuggestPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\TableRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\TextareaPromptRenderer;
use SwooleTW\Hyperf\Prompts\Themes\Default\TextPromptRenderer;

trait Themes
{
    /**
     * The name of the active theme.
     */
    protected static string $theme = 'default';

    /**
     * The available themes.
     *
     * @var array<string, array<class-string<\SwooleTW\Hyperf\Prompts\Prompt>, class-string<callable&object>>>
     */
    protected static array $themes = [
        'default' => [
            TextPrompt::class => TextPromptRenderer::class,
            TextareaPrompt::class => TextareaPromptRenderer::class,
            PasswordPrompt::class => PasswordPromptRenderer::class,
            SelectPrompt::class => SelectPromptRenderer::class,
            MultiSelectPrompt::class => MultiSelectPromptRenderer::class,
            ConfirmPrompt::class => ConfirmPromptRenderer::class,
            PausePrompt::class => PausePromptRenderer::class,
            SearchPrompt::class => SearchPromptRenderer::class,
            MultiSearchPrompt::class => MultiSearchPromptRenderer::class,
            SuggestPrompt::class => SuggestPromptRenderer::class,
            Spinner::class => SpinnerRenderer::class,
            Note::class => NoteRenderer::class,
            Table::class => TableRenderer::class,
            Progress::class => ProgressRenderer::class,
            Clear::class => ClearRenderer::class,
        ],
    ];

    /**
     * Get or set the active theme.
     *
     * @throws InvalidArgumentException
     */
    public static function theme(?string $name = null): string
    {
        if ($name === null) {
            return static::$theme;
        }

        if (! isset(static::$themes[$name])) {
            throw new InvalidArgumentException("Prompt theme [{$name}] not found.");
        }

        return static::$theme = $name;
    }

    /**
     * Add a new theme.
     *
     * @param array<class-string<\SwooleTW\Hyperf\Prompts\Prompt>, class-string<callable&object>> $renderers
     */
    public static function addTheme(string $name, array $renderers): void
    {
        if ($name === 'default') {
            throw new InvalidArgumentException('The default theme cannot be overridden.');
        }

        static::$themes[$name] = $renderers;
    }

    /**
     * Get the renderer for the current prompt.
     */
    protected function getRenderer(): callable
    {
        $class = get_class($this);

        return new (static::$themes[static::$theme][$class] ?? static::$themes['default'][$class])($this);
    }

    /**
     * Render the prompt using the active theme.
     */
    protected function renderTheme(): string
    {
        $renderer = $this->getRenderer();

        return $renderer($this);
    }
}
