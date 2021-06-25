<?php
/**
 * @package         Module
 * @subpackage      mod_example
 * @copyright       Copyright (C) pp, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

?>
<div class="weather-wrap"></div>

<script>
	jQuery('.weather-wrap').weatherHelper({ refresh: <?php echo $params->get('cache_lifetime');?> });
</script>