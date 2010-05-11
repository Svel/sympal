<?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/dashboard.css') ?>

<div id="sf_admin_container">
  
  <div id="sympal-dashboard">
    <h1>Add new content</h1>
    
    <ul class="new-content-type">
      <?php foreach ($contentTypes as $contentType): ?>
        <li>
          <a href="<?php echo url_for('@sympal_content_create_type?type='.$contentType->id) ?>" title="Create new <?php echo $contentType->label ?>">
            <?php echo $contentType->label ?>
          </a>
          <div class="desc">
            <?php echo $contentType->description ?>
          </div>
          
          <a href="<?php echo url_for('@sympal_content_create_type?type='.$contentType->id) ?>" class="create">Create</a>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="clear" style="clear: both;"></div>
  </div>
</div>