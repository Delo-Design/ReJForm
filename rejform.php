<?php defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Filesystem\Path;

class plgSystemRejform extends CMSPlugin
{


	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;


	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;


	/**
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	function onContentPrepareForm(Form $form, $data)
	{
		$paths_config = $this->params->get('paths', '');
		$form_name    = str_replace('.', '/', $form->getName());
		$find         = false;

		if (strpos($paths_config, ',') !== false)
		{
			$paths = explode(",", $paths_config);

		}
		else
		{
			$paths = explode("\n", $paths_config);
		}

		foreach ($paths as $path)
		{
			$file_form = Path::clean(JPATH_ROOT . '/' . $path . '/' . $form_name . '.xml');

			if (file_exists($file_form))
			{
				$find = true;
				break;
			}
		}

		if (!$find)
		{
			return;
		}

		if (file_exists($file_form))
		{
			$source = $form->getXml();
			$new    = simplexml_load_string(file_get_contents($file_form));
			self::mergeNodes($source, $new);
			$form->load($source->asXML());
		}

	}


	protected static function mergeNodes(\SimpleXMLElement $source, \SimpleXMLElement $new)
	{
		// The assumption is that the inputs are at the same relative level.
		// So we just have to scan the children and deal with them.

		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			if (isset($source[$name]))
			{
				$source[$name] = (string) $value;
			}
			else
			{
				$source->addAttribute($name, $value);
			}
		}

		foreach ($new->children() as $child)
		{
			$type = $child->getName();
			$name = $child['name'];

			// Does this node exist?
			$fields = $source->xpath($type . '[@name="' . $name . '"]');

			if (empty($fields))
			{
				$fields = $source->xpath($type);
			}

			if (empty($fields))
			{
				// This node does not exist, so add it.
				self::addNode($source, $child);
			}
			else
			{
				// This node does exist.
				switch ($type)
				{
					case 'field':
						self::mergeNode($fields[0], $child);
						break;

					default:
						self::mergeNodes($fields[0], $child);
						break;
				}
			}
		}
	}


	protected static function mergeNode(\SimpleXMLElement $source, \SimpleXMLElement $new)
	{
		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			if (isset($source[$name]))
			{
				$source[$name] = (string) $value;
			}
			else
			{
				$source->addAttribute($name, $value);
			}
		}
	}


	protected static function addNode(\SimpleXMLElement $source, \SimpleXMLElement $new)
	{
		// Add the new child node.
		$node = $source->addChild($new->getName(), htmlspecialchars(trim($new)));

		// Add the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			$node->addAttribute($name, $value);
		}

		// Add any children of the new node.
		foreach ($new->children() as $child)
		{
			self::addNode($node, $child);
		}
	}


}