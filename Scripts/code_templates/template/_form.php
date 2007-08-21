<form action="" method="post">
<label><?= ucfirst($Controller['name']) ?></label><input type="text" name='name' value="<?='<'?>?=$<?= $Controller['name'] ?>_data['name']?>" /><br />

<?='<'?>?php if($<?= $Controller['name'] ?>_data['id']?>) { ?>
<input type="hidden" name='id' value="<?='<'?>?=$<?= $Controller['name'] ?>_data['id']?>" />
<?='<'?>?php } ?>
<input name="action" value="<?='<'?>?=$action?>" type='submit' />
</form> 
