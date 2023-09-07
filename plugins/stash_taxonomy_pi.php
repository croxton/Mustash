<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_taxonomy_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_taxonomy_pi extends Mustash_plugin {

    /**
     * Name
     *
     * @var 	string
     * @access 	public
     */
    public $name = 'Taxonomy';

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
    protected $dependencies = array('Taxonomy');

    /**
     * Flag for entry status change
     *
     * @var 	string
     * @access 	private
     */
    private static $_entry_status_change = FALSE;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Add hooks
        $this->register_hook('before_channel_entry_update', array(), FALSE); // non-visible hook
        $this->register_hook('taxonomy_updated');
    }

    /**
     * Set groups for this object
     *
     * @access	protected
     * @return	array
     */
    protected function set_groups()
    {
        return stash_array_column($this->_get_trees(), 'label', 'id');
    }

    /*
    ================================================================
    Hooks
    ================================================================
    */

    /**
     * Hook: before_channel_entry_update
     *
     * @access	public
     * @param	EllisLab\ExpressionEngine\Model\Channel\ChannelEntry
     * @param	array
     * @param	array
     * @return	void
     */
    public function before_channel_entry_update($entry_obj, $data, $data_original)
    {
        // is the entry status changing?
        if ( isset($data_original['status']) )
        {
            self::$_entry_status_change = TRUE;
        }
    }

    /**
     * Hook: taxonomy_updated
     *
     * @access	public
     * @param	integer the tree ID that was edited
     * @param	string update_node|reorder_nodes|delete_branch|delete_node
     * @param	array
     * @return	void
     */
    public function taxonomy_updated($tree_id, $update_type, $data)
    {
        if ($update_type == 'fieldtype_save' AND FALSE === self::$_entry_status_change)
        {
            // entry was created/updated
            switch ($data['update_type'])
            {
                // node moved to new parent or new node
                case 'new_parent' : case 'new_node' :

                // always need to flush cache
                $this->flush_cache(__FUNCTION__, $tree_id);
                break;

                // node updated
                case 'node_update' :

                    if ( isset($data['old_node']) && isset($data['old_node']['label']) )
                    {
                        // node updated, but is the old node label the same as the new one?
                        if ( $data['node_data']['label'] != $data['old_node']['label'])
                        {
                            // yes: node label was changed, so we'll need to flush cache
                            $this->flush_cache(__FUNCTION__, $tree_id);
                        }
                    }
                    break;
            }
        }
        else
        {
            // tree was updated or entry status changed, so flush cache
            $this->flush_cache(__FUNCTION__, $tree_id);
        }
    }

    /*
    ================================================================
    Model
    ================================================================
    */

    /**
     * Get a list of Taxonomy trees
     *
     * @access	private
     * @return	string
     */
    private function _get_trees()
    {
        $result = ee()->db->select('id, label')
            ->from('taxonomy_trees')
            ->where('site_id', $this->site_id)
            ->order_by('label ASC')
            ->get();

        if ($result->num_rows() == 0)
        {
            return FALSE;
        }
        return $result->result_array();
    }

}