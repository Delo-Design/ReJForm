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
			$form->load(file_get_contents($file_form));
		}

	}


}