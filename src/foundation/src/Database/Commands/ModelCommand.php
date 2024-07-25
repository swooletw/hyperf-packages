<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Database\Commands;

use Hyperf\CodeParser\Project;
use Hyperf\Database\Commands\Ast\ModelUpdateVisitor;
use Hyperf\Database\Commands\ModelCommand as BaseModelCommand;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Stringable\Str;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;

use function Hyperf\Support\make;

class ModelCommand extends BaseModelCommand
{
    public function handle()
    {
        $table = $this->input->getArgument('table');
        $prefix = 'devtool.generator.model';

        $option = new ModelOption();
        $option->setPool($this->input->getOption('pool'))
            ->setPath($this->config->get("{$prefix}.path", 'app/Models'))
            ->setPrefix($this->config->get("{$prefix}.prefix", ''))
            ->setInheritance($this->config->get("{$prefix}.inheritance", 'Model'))
            ->setUses($this->config->get("{$prefix}.uses", \App\Models\Model::class))
            ->setForceCasts($this->config->get("{$prefix}.force-casts", false))
            ->setRefreshFillable($this->config->get("{$prefix}.refresh-fillable", false))
            ->setTableMapping($this->config->get("{$prefix}.table-mapping", []))
            ->setIgnoreTables($this->config->get("{$prefix}.ignore-tables", []))
            ->setWithComments($this->config->get("{$prefix}.with-comments", false))
            ->setWithIde($this->config->get("{$prefix}.with-ide", false))
            ->setVisitors($this->config->get("{$prefix}.visitors", []))
            ->setPropertyCase($this->config->get("{$prefix}.property-case"));

        if ($table) {
            $this->createModel($table, $option);
        } else {
            $this->createModels($option);
        }
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $table, string $name, ModelOption $option): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceUses($stub, $option->getUses())
            ->replaceClass($stub, $name)
            ->replaceTable($stub, $table);
    }

    protected function createModel(string $table, ModelOption $option)
    {
        $builder = $this->getSchemaBuilder($option->getPool());
        $table = Str::replaceFirst($option->getPrefix(), '', $table);
        $columns = $this->formatColumns($builder->getColumnTypeListing($table));
        if (empty($columns)) {
            $this->output?->error(
                sprintf('Query columns empty, maybe is table `%s` does not exist. You can check it in database.', $table)
            );
        }

        $project = new Project();
        $class = $option->getTableMapping()[$table] ?? Str::studly(Str::singular($table));
        $class = $project->namespace($option->getPath()) . $class;
        $path = BASE_PATH . '/' . $project->path($class);

        if (! file_exists($path)) {
            $this->mkdir($path);
            file_put_contents($path, $this->buildClass($table, $class, $option));
        }

        $columns = $this->getColumns($class, $columns, $option->isForceCasts());

        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(ModelUpdateVisitor::class, [
            'class' => $class,
            'columns' => $columns,
            'option' => $option,
        ]));
        $data = make(ModelData::class, ['class' => $class, 'columns' => $columns]);
        foreach ($option->getVisitors() as $visitorClass) {
            $traverser->addVisitor(make($visitorClass, [$option, $data]));
        }

        $traverser->addVisitor(new CloningVisitor());

        $originStmts = $this->astParser->parse(file_get_contents($path));
        $originTokens = $this->lexer->getTokens();
        $newStmts = $traverser->traverse($originStmts);

        $code = $this->printer->printFormatPreserving($newStmts, $originStmts, $originTokens);

        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model %s was created.</info>', $class));

        if ($option->isWithIde()) {
            $this->generateIDE($code, $option, $data);
        }
    }
}
