<?php
function show_event($event) {
	$json = json_decode($event->details);
	$details = hesc($event->details);
	switch($json->action) {
	case 'User add':
		$details = 'Added user';
		break;
	case 'Server add':
		$details = 'Added server to key management';
		break;
	case 'Group add':
		$details = 'Created group';
		break;
	case 'Account add':
		$details = 'Added account '.hesc($json->value);
		break;
	case 'Account remove':
		// Legacy event type
		$details = 'Removed account '.hesc($json->value);
		break;
	case 'Access request':
		$details = 'Requested access for '.show_event_participant($json->value);
		break;
	case 'Access approve':
		$details = 'Approved access for '.show_event_participant($json->value);
		break;
	case 'Access reject':
		$details = 'Rejected access for '.show_event_participant($json->value);
		break;
	case 'Access add':
		$details = 'Added access for '.show_event_participant($json->value);
		break;
	case 'Access remove':
		$details = 'Removed access for '.show_event_participant($json->value);
		break;
	case 'Administrator add':
		$details = 'Added administrator '.show_event_participant($json->value);
		break;
	case 'Administrator remove':
		$details = 'Removed administrator '.show_event_participant($json->value);
		break;
	case 'Member add':
		$details = 'Added member '.show_event_participant($json->value);
		break;
	case 'Member remove':
		$details = 'Removed member '.show_event_participant($json->value);
		break;
	case 'Pubkey add':
		$details = 'Added public key '.hesc($json->value);
		break;
	case 'Pubkey remove':
		$details = 'Removed public key '.hesc($json->value);
		break;
	case 'Setting update':
		$details = hesc($json->field).' changed from <q>'.hesc($json->oldvalue).'</q> to <q>'.hesc($json->value).'</q>';
		break;
	case 'Sync status change':
		$details = 'Sync status: '.hesc($json->value);
		break;
	}
	?>
	<tr>
		<td>
			<?php if(get_class($event) == 'ServerEvent') { ?>
			<a href="<?php outurl('/servers/'.urlencode($event->server->hostname))?>" class="server"><?php out($event->server->hostname) ?></a>
			<?php } elseif(get_class($event) == 'UserEvent') { ?>
			<a href="<?php outurl('/users/'.urlencode($event->user->uid))?>" class="user"><?php out($event->user->uid) ?></a>
			<?php } elseif(get_class($event) == 'ServerAccountEvent') { ?>
			<a href="<?php outurl('/servers/'.urlencode($event->account->server->hostname).'/accounts/'.urlencode($event->account->name))?>" class="serveraccount"><?php out($event->account->name.'@'.$event->account->server->hostname) ?></a>
			<?php } elseif(get_class($event) == 'GroupEvent') { ?>
			<a href="<?php outurl('/groups/'.urlencode($event->group->name))?>" class="group"><?php out($event->group->name) ?></a>
			<?php } ?>
		</td>
		<?php if(is_null($event->actor->uid)) { ?>
		<td>removed</td>
		<?php } else { ?>
		<td><a href="<?php outurl('/users/'.urlencode($event->actor->uid))?>" class="user"><?php out($event->actor->uid) ?></a></td>
		<?php } ?>
		<td><?php out($details, ESC_NONE) ?></td>
		<td class="nowrap"><?php out($event->date) ?></td>
	</tr>
	<?php
}
function show_event_participant($participant) {
	list($type, $name) = explode(':', $participant, 2);
	if($type == 'user') {
		return '<a href="'.rrurl('/users/'.urlencode($name)).'" class="user">'.hesc($name).'</a>';
	} elseif($type == 'account') {
		list($account, $server) = explode('@', $name, 2);
		return '<a href="'.rrurl('/servers/'.urlencode($server).'/accounts/'.urlencode($account)).'" class="serveraccount">'.hesc($name).'</a>';
	} elseif($type == 'group') {
		return '<a href="'.rrurl('/groups/'.urlencode($name)).'" class="group">'.hesc($name).'</a>';
	} else {
		return hesc($participant);
	}
}
function keygen_help($box_position) {
	?>
	<ul class="nav nav-tabs">
		<li><a href="#windows_instructions" data-toggle="tab">Windows</a></li>
		<li><a href="#mac_instructions" data-toggle="tab">Mac</a></li>
		<li><a href="#linux_instructions" data-toggle="tab">Linux</a></li>
	</ul>
	<div class="tab-content clearfix">
		<div class="tab-pane fade" id="windows_instructions">
			<aside class="pull-right"><img src="<?php outurl('/putty-key-generator.png')?>" class="img-rounded"></aside>
			<p>On Windows you will typically use the <a href="http://www.chiark.greenend.org.uk/~sgtatham/putty/download.html">PuTTYgen</a> application to generate your key pair.</p>
			<ol>
				<li>Download and run the latest Windows installer from the <a href="http://www.chiark.greenend.org.uk/~sgtatham/putty/download.html">PuTTY download page</a>.
				<li>Start PuTTYgen.
				<li>Select the type of key to generate. RSA, ECDSA or ED25519 are good choices.
				<li>For RSA, enter "4096" as the number of bits in the generated key. For ECDSA, use either the nistp384 or nistp521 curve.
				<li>Click the Generate button.
				<li>Provide a comment for the key: This comment allows to identify your key. Best thing is to use your username or email address.
				<li><strong>Provide a key passphrase.</strong>
				<li>Save the private key to your local machine.
				<li>Select and copy the contents of the "Public key for pasting into OpenSSH authorized_keys file" section at the top of the window (scrollable, make sure to select all).
				<?php if(!is_null($box_position)) { ?>
				<li>Paste the public key that you just copied into the box <?php out($box_position)?> and click the "Add public key" button.
				<?php } ?>
			</ol>
			<div class="alert alert-info">
				<strong>Note:</strong> if you are not using PuTTY to connect, you may need to export your private key into OpenSSH format to use it. You can do this from the Conversions menu.
			</div>
			<div class="alert alert-info">
				<strong>Note:</strong> if you are using Cygwin or MSYS bash, the instructions for Linux can be used instead.
			</div>
		</div>
		<div class="tab-pane fade" id="mac_instructions">
			<p>On Mac you can generate a key pair with the ssh-keygen command.</p>
			<ol>
				<li>Start the "Terminal" program.
				<li>Run one of the following commands. Make sure to replace '<var>comment</var>' with a text that identifies you. Best thing is to use your username or email address.
					<ul>
						<li>rsa: <code>ssh-keygen -t rsa -b 4096 -C '<var>comment</var>'</code>
						<li>ecdsa: <code>ssh-keygen -t ecdsa -b 256 -C '<var>comment</var>'</code>
						<li>ed25519: <code>ssh-keygen -t ed25519 -C '<var>comment</var>'</code>
					</ul>
				<li><strong>Make sure that you give the key a passphrase when prompted.</strong>
				<li>A new text file will have been created in a <code>.ssh</code> directory. Copy the contents of one of the following files into your clipboard (depends on the algorithm you used).
					<ul>
						<li>rsa: <code>id_rsa.pub</code>
						<li>ecdsa: <code>id_ecdsa.pub</code>
						<li>ed25519: <code>id_ed25519.pub</code>
					</ul>
				<?php if(!is_null($box_position)) { ?>
				<li>Paste the public key that you just copied into the box <?php out($box_position)?> and click the "Add public key" button.
				<?php } ?>
			</ol>
		</div>
		<div class="tab-pane fade" id="linux_instructions">
			<p>On Linux you can generate a key pair with the ssh-keygen command.</p>
			<ol>
				<li>Open a terminal on your machine
				<li>Run one of the following commands. Make sure to replace '<var>comment</var>' with a text that identifies you. Best thing is to use your username or email address.
					<ul>
						<li>rsa: <code>ssh-keygen -t rsa -b 4096 -C '<var>comment</var>'</code>
						<li>ecdsa: <code>ssh-keygen -t ecdsa -b 256 -C '<var>comment</var>'</code>
						<li>ed25519: <code>ssh-keygen -t ed25519 -C '<var>comment</var>'</code>
					</ul>
					<div class="alert alert-info">
						Note: if this command fails with a message of "ssh-keygen: command not found", you need to install the openssh-client package: <code>sudo apt-get install openssh-client</code> on Debian-based systems.
					</div>
				<li><strong>Make sure that you give the key a passphrase when prompted.</strong>
				<li>A new text file will have been created in a <code>.ssh</code> directory. Copy the contents of one of the following files into your clipboard (depends on the algorithm you used).
					<ul>
						<li>rsa: <code>id_rsa.pub</code>
						<li>ecdsa: <code>id_ecdsa.pub</code>
						<li>ed25519: <code>id_ed25519.pub</code>
					</ul>
				<?php if(!is_null($box_position)) { ?>
				<li>Paste the public key that you just copied into the box <?php out($box_position)?> and click the "Add public key" button.
				<?php } ?>
			</ol>
		</div>
	</div>
	<?php
}

function pubkey_json($pubkey, $include_keydata = true, $include_owner = true) {
	$json = new StdClass;
	if($include_keydata) {
		$json->keydata = $pubkey->export();
	}
	$json->type = $pubkey->type;
	$json->keysize = $pubkey->keysize;
	$json->fingerprint = $pubkey->fingerprint_md5;
	$json->fingerprint_md5 = $pubkey->fingerprint_md5;
	$json->fingerprint_sha256 = $pubkey->fingerprint_sha256;
	$json->upload_date = $pubkey->upload_date;
	if($include_owner) {
		$json->owner = new StdClass;
		$json->owner->type = get_class($pubkey->owner);
		if(get_class($pubkey->owner) == 'User') {
			$json->owner->uid = $pubkey->owner->uid;
		} elseif(get_class($pubkey->owner) == 'ServerAccount') {
			$json->owner->hostname = $pubkey->owner->server->hostname;
		}
		$json->owner->name = $pubkey->owner->name;
	}
	return $json;
}