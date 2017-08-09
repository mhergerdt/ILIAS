<?php

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);
		$tpl = $this->getTemplate('tpl.popover.html', true, true);
		$tpl->setVariable('FORCE_RENDERING', '');
		/** @var Component\Popover\Popover $component */

		$replacement = array(
			'"'=> '\"',
			"\n"=>"",
			"\t"=>"",
			"\r"=>"",
		);

		$options = array(
			'title'     => $this->escape($component->getTitle()),
			'placement' => $component->getPosition(),
			'multi'     => true,
			'template'  => str_replace(array_keys($replacement), array_values($replacement), $tpl->get()),
		);

		$is_async = $component->getAsyncContentUrl();
		if ($is_async) {
			$options['type'] = 'async';
			$options['url'] = $component->getAsyncContentUrl();
		}

		$show = $component->getShowSignal();
		$replace = $component->getReplaceContentSignal();

		$component = $component->withAdditionalOnLoadCode(function($id) use ($options, $show, $replace, $is_async) {
			if (!$is_async) {
				$options["url"] = "#{$id}";
			}
			$options = json_encode($options);

			return
				"$(document).on('{$show}', function(event, signalData) {
					il.UI.popover.showFromSignal(signalData, JSON.parse('{$options}'));
				});".
				"$(document).on('{$replace}', function(event, signalData) {
					il.UI.popover.replaceContentFromSignal('{$show}', signalData);
				});";
		});

		$id = $this->bindJavaScript($component);

		if ($component->getAsyncContentUrl()) {
			return '';
		}

		if ($component instanceof Component\Popover\Standard) {
			return $this->renderStandardPopover($component, $default_renderer, $id);
		} else {
			if ($component instanceof Component\Popover\Listing) {
				return $this->renderListingPopover($component, $default_renderer, $id);
			}
		}

		return '';
	}


	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/libs/node_modules/webui-popover/dist/jquery.webui-popover.min.js');
		$registry->register('./src/UI/templates/js/Popover/popover.js');
	}


	/**
	 * @param Component\Popover\Standard $popover
	 * @param RendererInterface          $default_renderer
	 * @param string                     $id
	 *
	 * @return string
	 */
	protected function renderStandardPopover(Component\Popover\Standard $popover, RendererInterface $default_renderer, $id) {
		$tpl = $this->getTemplate('tpl.standard-popover-content.html', true, true);
		$tpl->setVariable('ID', $id);
		$tpl->setVariable('CONTENT', $default_renderer->render($popover->getContent()));

		return $tpl->get();
	}


	/**
	 * @param Component\Popover\Listing $popover
	 * @param RendererInterface         $default_renderer
	 * @param string                    $id
	 *
	 * @return string
	 */
	protected function renderListingPopover(Component\Popover\Listing $popover, RendererInterface $default_renderer, $id) {
		$tpl = $this->getTemplate('tpl.listing-popover-content.html', true, true);
		$tpl->setVariable('ID', $id);
		foreach ($popover->getItems() as $item) {
			$tpl->setCurrentBlock('item');
			$tpl->setVariable('ITEM', $default_renderer->render($item));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function escape($str) {
		return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array( Component\Popover\Standard::class, Component\Popover\Listing::class );
	}
}
