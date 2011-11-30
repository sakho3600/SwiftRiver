<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * River Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to GPLv3 license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/gpl.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   SwiftRiver - http://github.com/ushahidi/Swiftriver_v2
 * @subpackage Controllers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU General Public License v3 (GPLv3) 
 */
class Controller_River extends Controller_Swiftriver {

	/**
	 * Channels
	 */
	protected $channels;

	/**
	 * @return	void
	 */
	public function before()
	{
		// Execute parent::before first
		parent::before();

		// Get all available channels from plugins
		$this->channels = Swiftriver_Plugins::channels();
	}

	/**
	 * @return	void
	 */
	public function action_index()
	{
		$this->template->content = View::factory('pages/river/main')
			->bind('river', $river)
			->bind('droplets', $filter_total)
			->bind('meter', $meter)
			->bind('filters', $filters)
			->bind('settings', $settings)
			->bind('more', $more);

		// First we need to make sure this river
		// actually exists
		$id = (int) $this->request->param('id', 0);
		
		$river = ORM::factory('river')
			->where('id', '=', $id)
			->where('account_id', '=', $this->account->id)
			->find();
		if ( ! $river->loaded())
		{
			// It doesn't -- redirect back to dashboard
			$this->request->redirect('dashboard');
		}			
		
		// This River's total droplets
		$total_droplets = $river->droplets->count_all();

		// Build River Query
		$query = DB::select(array(DB::expr('DISTINCT droplets.id'), 'id'))
			->from('droplets');
		$query->join('rivers_droplets', 'INNER')
			->on('rivers_droplets.droplet_id', '=', 'droplets.id');
		$query->where('rivers_droplets.river_id', '=', $river->id);
		$query->order_by('droplet_date_pub', 'DESC');

		// Clone query before any filters have been applied
		$pre_filter = clone $query;
		$total = (int) $pre_filter->execute()->count();

		// SwiftRiver Plugin Hook -- Hook into River Droplet Query
		Swiftriver_Event::run('swiftriver.river.filter', $query);

		// Get our droplets
		$droplets = $query->execute()->as_array();
		$filter_total = (int) count($droplets);

		// Droplets Meter
		$meter = 0;
		if ($total)
		{
			$meter = ($filter_total / $total) * 100;
		}

		$filters = url::site().$this->account->account_path.'/river/filters/'.$id;
		$settings = url::site().$this->account->account_path.'/river/settings/'.$id;
		$more = url::site().$this->account->account_path.'/river/more/';
	}

	/**
	 * Create a New River
	 *
	 * @return	void
	 */
	public function action_new()
	{
		$this->template->content = View::factory('pages/river/new');
		$this->template->header->js = View::factory('pages/river/js/new');
		$this->template->header->js->settings = url::site().$this->account->account_path.'/river/settings/';

		// save the river
		if ($_POST)
		{
			$river = ORM::factory('river');
			$post = $river->validate($_POST);

			if ($post->check())
			{
				$river->river_name = $post['river_name'];
				$river->account_id = $this->account->id;
				$river->save();

				// Always redirect after a successful POST to prevent refresh warnings
				$this->request->redirect('river/index/'.$river->id);
			}
			else
			{
				//validation failed, get errors
				$errors = $post->errors('river');
			}

		}
	}

	/**
	 * Ajax rendered filter control box
	 * 
	 * @return	void
	 */
	public function action_filters()
	{
		$this->template = '';
		$this->auto_render = FALSE;
		echo View::factory('pages/river/filters_control');
	}

	/**
	 * Ajax rendered settings control box
	 * 
	 * @return	void
	 */
	public function action_settings()
	{
		$id = (int) $this->request->param('id', 0);
		
		// Get River (if set)
		$river = ORM::factory('river')
			->where('id', '=', $id)
			->where('account_id', '=', $this->account->id)
			->find();

		$this->template = '';
		$this->auto_render = FALSE;
		$settings = View::factory('pages/river/settings_control');
		$settings->channels = $this->channels;
		$settings->river = $river;
		echo $settings;
	}

	/**
	 * Ajax rendered more control box
	 * 
	 * @return	void
	 */
	public function action_more()
	{
		$this->template = '';
		$this->auto_render = FALSE;
		echo View::factory('pages/river/more_control');
	}	

	/**
	 * Ajax Title Editing Inline
	 *
	 * Edit River Name
	 * 
	 * @return	void
	 */
	public function action_ajax_title()
	{
		$this->template = '';
		$this->auto_render = FALSE;

		// check, has the form been submitted, if so, setup validation
		if ($_REQUEST AND
			isset($_REQUEST['edit_id'], $_REQUEST['edit_value']) AND
			! empty($_REQUEST['edit_id']) AND 
			! empty($_REQUEST['edit_value']) )
		{

			$river = ORM::factory('river')
				->where('id', '=', $_REQUEST['edit_id'])
				->where('account_id', '=', $this->account->id)
				->find();

			if ($river->loaded())
			{
				$river->river_name = $_REQUEST['edit_value'];
				$river->save();
			}
		}
	}

	/**
	 * Ajax Delete River
	 * 
	 * @return string - json
	 */
	public function action_ajax_delete()
	{
		$this->template = '';
		$this->auto_render = FALSE;
		if ( $_REQUEST AND isset($_REQUEST['id']) AND
			! empty($_REQUEST['id']) )
		{
			$river = ORM::factory('river')
				->where('id', '=', $_REQUEST['id'])
				->where('account_id', '=', $this->account->id)
				->find();

			if ($river->loaded())
			{
				$river->delete();
				echo json_encode(array("status"=>"success"));
			}
			else
			{
				echo json_encode(array("status"=>"error"));
			}
		}
		else
		{
			echo json_encode(array("status"=>"error"));
		}
	}
}