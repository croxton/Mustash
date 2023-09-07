<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_member_model_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_member_model_pi extends Mustash_plugin {

    /**
     * Name
     *
     * @var 	string
     * @access 	public
     */
    public $name = 'Member Model';

    /**
     * Version
     *
     * @var 	string
     * @access 	public
     */
    public $version = '2.0.0';

    /**
     * Extension hook priority
     *
     * @var 	integer
     * @access 	public
     */
    public $priority = '10';

    /**
     * Required modules
     *
     * @var 	integer
     * @access 	protected
     */
    protected $dependencies = array('Member');

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        ee()->load->model('member_model');

        // markers shared by all hooks
        $shared_markers = array(
            'member_id',
            'screen_name',
            'username',
            'group_id',
            'group_title'
        );

        // add hooks
        $this->register_hook('@all', $shared_markers);
        $this->register_hook('after_member_insert', $shared_markers);
        $this->register_hook('after_member_update', $shared_markers);
        $this->register_hook('after_member_delete', $shared_markers);
    }

    /**
     * Set groups for this object
     *
     * @access	protected
     * @return	array
     */
    protected function set_groups()
    {
        return stash_array_column($this->_get_cp_member_groups(), 'group_title', 'group_id');
    }

    /*
    ================================================================
    Hooks
    ================================================================
    */

    /**
     * Hook: member_create_end
     *
     * @access	public
     * @param	integer
     * @param	array
     * @param	array
     * @return	void
     */
    public function after_member_insert($member_obj, $data)
    {
        $markers = $this->_prep_markers($data);
        $this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
    }


    /**
     * Hook: member_update_end
     *
     * @access	public
     * @param	integer
     * @param	array
     * @return	void
     */
    public function after_member_update($member_obj, $data, $data_original)
    {
        // we want to flush variables associated with the values of this member account *before* it was edited
        $data = array_merge($data, $data_original);

        // prep markers and flush cache
        $markers = $this->_prep_markers($data);
        $this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
    }

    /**
     * Hook: member_delete
     *
     * @access	public
     * @param	array
     * @return	array
     */
    public function after_member_delete($member_obj, $data)
    {
        $markers = $this->_prep_markers($data);
        $this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
    }

    /**
     * Prep markers for rule parsing
     *
     * @access	private
     * @param	array
     * @return	array
     */
    private function _prep_markers($data)
    {
        /* $data:
        Array
        (
            [member_id] => 5
            [group_id] => 1
            [username] => jsmith
            [screen_name] => John Smith
            ...
        )
        */
        return array(
            'member_id' 	=> $data['member_id'],
            'screen_name'	=> $data['screen_name'],
            'username'		=> $data['username'],
            'group_id'		=> $data['group_id'],
            'group_title'	=> $this->_get_member_group_title($data['group_id']),
        );
    }

    /*
    ================================================================
    Model
    ================================================================
    */

    /**
     * Get a list of member groups that can access the control panel
     *
     * @access	private
     * @param	array
     */
    private function _get_cp_member_groups()
    {
        $result = ee()->db->select('group_id, group_title')
            ->from('member_groups')
            ->where('can_access_cp', 'y')
            ->order_by('group_title', 'asc')
            ->get();

        if ($result->num_rows() == 0)
        {
            return FALSE;
        }
        return $result->result_array();
    }

    /**
     * Get a member group title
     *
     * @access	private
     * @param	integer
     * @return	string
     */
    private function _get_member_group_title($group_id)
    {
        $result = ee()->db->select('group_title')
            ->from('member_groups')
            ->where('group_id', $group_id)
            ->where('site_id', $this->site_id)
            ->get();

        if ($result->num_rows() == 0)
        {
            return FALSE;
        }

        $row = $result->row();
        return $row->group_title;
    }
}



