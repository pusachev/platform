<?php

namespace Oro\Bundle\WorkflowBundle\Button;

use Oro\Bundle\ActionBundle\Button\ButtonContext;
use Oro\Bundle\ActionBundle\Button\ButtonInterface;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

abstract class AbstractTransitionButton implements ButtonInterface
{
    const DEFAULT_TEMPLATE = 'OroWorkflowBundle::Button\transitionButton.html.twig';
    const TRANSITION_JS_DIALOG_WIDGET = 'oroworkflow/transition-dialog-widget';

    /** @var Workflow */
    protected $workflow;

    /** @var Transition */
    protected $transition;

    /*** @var ButtonContext */
    protected $buttonContext;

    /**
     * @param Transition $transition
     * @param Workflow $workflow
     * @param ButtonContext $buttonContext
     */
    public function __construct(Transition $transition, Workflow $workflow, ButtonContext $buttonContext)
    {
        $this->transition = $transition;
        $this->workflow = $workflow;
        $this->buttonContext = $buttonContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return sprintf('%s_%s', $this->workflow->getName(), $this->transition->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->transition->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        $frontendOptions = $this->transition->getFrontendOptions();

        return isset($frontendOptions['icon']) ? $frontendOptions['icon'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->workflow->getDefinition()->getPriority();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return static::DEFAULT_TEMPLATE;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateData(array $customData = [])
    {
        $showDialog = $this->transition->getDisplayType() !== 'page';

        return array_merge(
            [
                'frontendOptions' => $this->transition->getFrontendOptions(),
                'hasForm' => $this->transition->hasForm(),
                'showDialog' => $showDialog,
                'routeParams' => [
                    'workflowName' => $this->workflow->getName(),
                    'transitionName' => $this->transition->getName(),
                    'entityClass' => $this->buttonContext->getEntityClass(),
                    'entityId' => $this->buttonContext->getEntityId(),
                    'route' => $this->buttonContext->getRouteName(),
                    'datagrid' => $this->buttonContext->getDatagridName(),
                    'group' => $this->buttonContext->getGroup(),
                ],
                'executionRoute' => $this->buttonContext->getExecutionRoute(),
                'dialogRoute' => $showDialog
                    ? $this->buttonContext->getFormDialogRoute()
                    : $this->buttonContext->getFormPageRoute(),
                'additionalData' => $this->getDatagridData(),
                'jsDialogWidget' => static::TRANSITION_JS_DIALOG_WIDGET,
            ],
            $customData
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagridData()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonContext()
    {
        return $this->buttonContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return ButtonInterface::DEFAULT_GROUP;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationDomain()
    {
        return 'workflows';
    }

    /**
     * @return Workflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * @return Transition
     */
    public function getTransition()
    {
        return $this->transition;
    }
}
