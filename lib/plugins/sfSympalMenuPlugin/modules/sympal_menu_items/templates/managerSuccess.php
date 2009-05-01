<?php set_sympal_title('Sympal Menu Manager') ?>

<?php use_helper('SympalMenuManager') ?>

<?php use_sympal_yui_css('fonts/fonts') ?>
<?php use_sympal_yui_css('treeview/assets/skins/sam/treeview') ?>
<?php use_sympal_yui_css('menu/assets/skins/sam/menu') ?>

<?php use_sympal_yui_js('yahoo-dom-event/yahoo-dom-event') ?>
<?php use_sympal_yui_js('treeview/treeview') ?>
<?php use_sympal_yui_js('event/event-min') ?>
<?php use_sympal_yui_js('dragdrop/dragdrop') ?>
<?php use_sympal_yui_js('connection/connection') ?>
<?php use_sympal_yui_js('container/container_core') ?>
<?php use_sympal_yui_js('menu/menu') ?>
<?php use_sympal_yui_js('connection/connection') ?>

<?php use_javascript('/sfSympalPlugin/js/bubbling/dispatcher/dispatcher-min') ?>

<div id="sympal_menu_manager">
  <h1>Sympal Menu Manager</h1>

  <div id="top_menu">
    <ul>
      <li><?php echo image_tag('/sf/sf_admin/images/add.png').' '.link_to('Add New Menu', '@sympal_menu_items_new') ?></li>

      <?php if ($menuItem && !$menuItem->is_primary): ?>
        <li><?php echo image_tag('/sf/sf_admin/images/delete.png') ?> <?php echo link_to('Delete Menu', '@sympal_menu_manager_tree_delete?slug='.$menuItem['slug']) ?></li>
	    <?php endif; ?>

      <?php if ($menuItem): ?>
  	    <li><?php echo image_tag('/sfSympalPlugin/images/expand.gif') ?> <a id="expand" href="#">Expand all</a></li>
  	    <li><?php echo image_tag('/sfSympalPlugin/images/collapse.gif') ?> <a id="collapse" href="#">Collapse all</a></li>
	    <?php endif; ?>
	    <li><?php echo image_tag('/sf/sf_admin/images/list.png').' '.link_to('Traditional List', '@sympal_menu_items') ?></li>
  	</ul>
  	<span id="loading"></span>
  </div>
  <div id="controls">
    Move dragged menu items 
    <select id="move_action">
      <option>After</option>
      <option>Before</option>
      <option>Under</option>
    </select>
    the item you dragged it to.
  </div>

  <?php if ($menuItem && $menuItem->exists()): ?>
    <h2>Managing <?php echo $menuItem['name'] ?> Menu</h2>

    <div id="sympal_menu_manager_tree_holder">
      <?php echo get_sympal_menu_manager_html($menuItem) ?>
    </div>

    <?php echo get_sympal_menu_manager_js($menuItem) ?>
  <?php else: ?>
    <p>Primary menu has not been created yet.</p>
  <?php endif; ?>
</div>