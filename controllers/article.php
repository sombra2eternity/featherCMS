<?php
	if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
		case 'comment.add':
			if(!isset($_POST['articleID'])){break;}
			$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){break;}
			include_once('api.articles.php');
			$articleOB = articles_getSingle('(id = '.$aID.')');
			$_POST = array_merge($_POST,array('commentChannel'=>$articleOB['id'],'commentAuthor'=>$GLOBALS['user']['userNick'],'commentReview'=>1));
			$r = article_comment_save($_POST);if(isset($r['errorDescription'])){print_r($r);exit;}
			header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);exit;
		case 'ajax.comment.remove':
			if(!isset($_POST['commentID'])){break;}
			$cID = preg_replace('/[^0-9]*/','',$_POST['commentID']);if(empty($cID)){break;}
			include_once('api.articles.php');
			$r = article_comment_deleteWhere('(id = '.$cID.')');if(isset($r['errorDescription'])){print_r($r);exit;}
			echo json_encode(array('errorCode'=>'0'));exit;
	}}

	function article_main(){
		include_once('api.articles.php');
		$r = articles_updateSchema();
		var_dump($r);
		exit;
	}

	function article_list($mod = ''){
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		include_once('api.articles.php');
		include_once('inc.presentation.php');
		include_once('inc.requests.php');
		$currentController = str_replace('_','/',__FUNCTION__);
		$articlesPerPage = 20;

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'articleSetThumb':
				if(!isset($_POST['articleID']) || (!isset($_POST['articleImageSmall']) && !isset($_POST['articleImageMedium']) && !isset($_POST['articleImageLarge']))){break;}
				$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){$aID = false;break;}
				$r = article_thumb_set($aID,$_POST);
				if(isset($r['errorDescription'])){print_r($r);exit;}
				header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);exit;
			case 'articleRemove':
				if(!isset($_POST['articleID'])){break;}
				$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){$aID = false;break;}
				$r = articles_remove($aID);if(isset($r['errorDescription'])){print_r($r);exit;}
				header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);exit;
			case 'ajax.articleRemove':
				if(!isset($_POST['articleID'])){break;}
				$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){echo json_encode(array('errorDescription'=>'INVALID_ARTICLE_ID','file'=>__FILE__,'line'=>__LINE__));exit;}
				$r = articles_remove($aID);if(isset($r['errorDescription'])){print_r($r);exit;}
				echo json_encode(array('errorCode'=>'0'));exit;
			case 'articlePublish':
				if(!isset($_POST['articleID'])){break;}
				$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){$aID = false;break;}
				$r = articles_publish($aID);if(isset($r['errorDescription'])){print_r($r);exit;}
				header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);exit;
			case 'articleUnpublish':
				if(!isset($_POST['articleID'])){break;}
				$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){$aID = false;break;}
				$r = articles_unpublish($aID);if(isset($r['errorDescription'])){print_r($r);exit;}
				header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);exit;
			case 'ajax.articlePublishScheduled':
				if(!isset($_POST['articleID'])){break;}
				$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){echo json_encode(array('errorDescription'=>'INVALID_ARTICLE_ID','file'=>__FILE__,'line'=>__LINE__));exit;}
				$date = preg_replace('/[^0-9\-]*/','',$_POST['articlePublicationDate']);if(!preg_match('/(?<year>[0-9]{4})\-(?<month>[0-9]+)\-(?<day>[0-9]+)/',$date,$m)){echo json_encode(array('errorDescription'=>'INVALID_DATE','file'=>__FILE__,'line'=>__LINE__));exit;}
				$date = $m['year'].'-'.str_pad($m['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($m['day'],2,'0',STR_PAD_LEFT);
				//FIXME: if !strtotime
				$r = articles_publishScheduled($aID,$date);
				echo json_encode(array('errorCode'=>'0'));exit;
			case 'commentAdd':
				if(!isset($_POST['articleID'])){break;}
				$aID = preg_replace('/[^0-9]*/','',$_POST['articleID']);if(empty($aID)){$aID = false;break;}
				$comment = array('commentChannel'=>$aID,'commentAuthor'=>$GLOBALS['user']['userNick'],'commentText'=>$_POST['commentText'],'commentReview'=>1);
				$r = article_comment_save($comment);
				if(isset($r['errorDescription'])){print_r($r);exit;}
				exit;
			case 'ajax.commentRemove':
				if(!isset($_POST['commentID'])){break;}
				$cID = preg_replace('/[^0-9]*/','',$_POST['commentID']);if(empty($cID)){break;}
				$r = article_comment_deleteWhere('(id = '.$cID.')');
				if(isset($r['errorDescription'])){print_r($r);exit;}
				echo json_encode(array('errorCode'=>'0'));exit;
			case 'commentBanIP':
				if(!isset($_POST['commentID'])){break;}
				$cID = preg_replace('/[^0-9]*/','',$_POST['commentID']);if(empty($cID)){break;}
				$comment = article_comment_getSingle('(id = '.$cID.')');if(!$comment){break;}
				$r = article_ban_save(array('banTarget'=>'ip:'.$comment['commentIP'],'banType'=>'comments-disabled'));
				if(isset($r['errorDescription'])){print_r($r);exit;}
				header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);exit;
			case 'commentApprove':
				if(!isset($_POST['commentID'])){break;}
				$cID = preg_replace('/[^0-9]*/','',$_POST['commentID']);if(empty($cID)){break;}
				$comment = article_comment_getSingle('(id = '.$cID.')');if(!$comment){break;}
				$params = array('_id_'=>$cID,'commentReview'=>1);
				$r = article_comment_save($params);
				if(isset($r['errorDescription'])){print_r($r);exit;}
				header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);exit;
		}}

		if(isset($_GET['criteria'])){$mod = 'search';}

		switch($mod){
			case 'draft':
				$articles = articles_getWhere('(articleIsDraft = 1)',array('order'=>'articleDate DESC,articleTime DESC','limit'=>(($GLOBALS['currentPage']-1)*$articlesPerPage).','.$articlesPerPage));
				$r = articles_getSingle('(articleIsDraft = 1)',array('selectString'=>'count(*) as count'));
				$total = $r['count'];
				break;
			case 'search':
				if(!isset($_GET['criteria'])){echo 45;exit;}
				$articles = articles_search($_GET['criteria']);
				$total = count($articles);
				break;
			default:
				$articles = articles_getWhere(1,array('order'=>'articleDate DESC,articleTime DESC','limit'=>(($GLOBALS['currentPage']-1)*$articlesPerPage).','.$articlesPerPage));
				$r = articles_getSingle(1,array('selectString'=>'count(*) as count'));
				$total = $r['count'];
		}
		/* Imágenes de los artículos */
		$images = article_image_getWhere('(articleID IN ('.implode(',',array_keys($articles)).'))');
		foreach($images as $k=>$image){$articles[$image['articleID']]['articleImages'][$k] = $image;}
		/* Comentarios de artículos */
		$comments = article_comment_getWhere('(commentChannel IN ('.implode(',',array_keys($articles)).'))');
		$commentsByChannel = array();foreach($comments as $comment){$commentsByChannel[$comment['commentChannel']][] = $comment;}
		/* Obtenemos los baneos de usuarios */
//FIXME: TODO
		/* Publicaciones Programadas */
		$reqs = requests_getWhere('(requestLock = \'publishScheduled\')');
		foreach($reqs as $req){$aID = substr($req['requestParams'],2,-2);if(isset($articles[$aID])){$articles[$aID]['articlePublishDate'] = $req['requestDate'];}}


		$s = '';
		foreach($articles as $article){
			$GLOBALS['replaceIteration'] = 0;
			$article['articleURL'] = presentation_helper_getArticleURL($article);
			if(isset($article['articleImages'])){$article['json.articleImages'] = json_encode($article['articleImages']);}
			if(isset($article['articleSnippetImage']) && strlen($article['articleSnippetImage']) > 3 && substr($article['articleSnippetImage'],0,1) == '{'){
				$im = json_decode($article['articleSnippetImage'],1);
				if(isset($im['articleImageSmall'])){$article['html.articleThumb'] = '<img src="{%baseURL%}article/image/'.$article['id'].'/'.$im['articleImageSmall'].'/64"/>';}
			}

			if(isset($article['articleIsDraft']) && $article['articleIsDraft']){$article['html.articleIsDraft'] = '<span class="draft">Borrador</span>';$article['html.articleIsDraftClass'] = 'draft';$article['html.option.publish'] = common_loadSnippet('article/snippets/article.node.option.publish');}
			else{$article['html.option.unpublish'] = common_loadSnippet('article/snippets/article.node.option.unpublish');}

			if(isset($article['articlePublishDate'])){$article['html.articlePublishDate'] = '<i class="icon-calendar"></i> El artículo se publicará el '.$article['articlePublishDate'];}
			if(isset($commentsByChannel[$article['id']])){
				$article['html.comments'] = '';
				foreach($commentsByChannel[$article['id']] as $comment){$article['html.comments'] .= common_loadSnippet('article/snippets/comment.node',$comment);}
			}
			$article['articleSnippet'] = preg_replace('/\{%image:[^%]*%?\}?/sm',' ',$article['articleSnippet']);
			$s .= common_loadSnippet('article/snippets/article.node',$article);
		}
		$TEMPLATE['list.articles'] = $s;

		/* INI-Paginador */
		$pager = '<div class="btn-group pager">';
		if($GLOBALS['currentPage'] > 1){$pager .= '<a class="btn btn-small" href="{%baseURL%}'.$currentController.'/page/'.($GLOBALS['currentPage']-1).'"><i class="icon-chevron-left"></i> Anterior</a>';}
		$pager .= '<a class="btn btn-small" href="{%baseURL%}'.$currentController.'/page/'.($GLOBALS['currentPage']+1).'">Siguiente <i class="icon-chevron-right"></i></a>';
		$pager .= '</div>';
		$TEMPLATE['pager'] = $pager;
		/* END-Paginador */

		common_loadScript('{%w.featherURL%}js/c/article.list.js');
		common_loadScript('{%w.featherURL%}js/upload.chain.js');
		common_loadScript('{%w.featherURL%}js/md5.js');
		common_loadScript('{%w.featherURL%}js/widget.calendar.js');
		common_renderTemplate('article/list');
	}

	function article_edit($aID = false){
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		include_once('api.articles.php');
		$articleOB = false;
		if($aID){do{
			$aID = preg_replace('/[^0-9]*/','',$aID);
			if(empty($aID)){$aID = false;break;}
			$articleOB = articles_getSingle('(id = '.$aID.')');
			if(!$articleOB){$aID = false;break;}
			$articleOB['articleTitle.value'] = str_replace(array('"'),array('&quot;'),$articleOB['articleTitle']);
			$articleOB['user'] = article_author_getByAuthorAlias($articleOB['articleAuthor']);
			$articleOB['articleImages'] = article_image_getWhere('(articleID = '.$aID.')');
			$articleOB['articleImagesJSON'] = json_encode($articleOB['articleImages']);
			$articleOB['articleAttachments'] = article_file_getWhere('(articleID = '.$aID.')');
			$articleOB['articleAttachmentsJSON'] = json_encode($articleOB['articleAttachments']);
		}while(false);}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'transfer.fragment':
				if(!$articleOB){echo json_encode(array('errorDescription'=>'ARTICLE_NOT_FOUND','file'=>__FILE__,'line'=>__LINE__));exit;}
				include_once('inc.uploadchain.php');
				$_params = array('file_name','file_size','file_parts','fragment_num','fragment_src','fragment_sum','fragment_len');
				foreach($_params as $param){if(!isset($_POST[$param]) || $_POST[$param] === ''){print_r(array('errorDescription'=>'INVALID_PARAMS:'.$param,'file'=>__FILE__,'line'=>__LINE__));exit;}}
				$r = uploadchain_fragment($_POST);if(isset($r['errorDescription'])){echo json_encode($r);exit;}
				if(isset($r['filePath'])){$r = article_file_save($articleOB['id'],$r);}
				echo json_encode($r);exit;
			case 'ajax.article.save.props':
				if($articleOB){$_POST['_id_'] = $articleOB['id'];}else{unset($_POST['_id_']);$_POST['articleAuthor'] = $GLOBALS['user']['userNick'];}
				$r = articles_save($_POST);if(isset($r['errorDescription'])){print_r($r);exit;}
				$return = array('errorCode'=>'0','data'=>$r);
				if(!$articleOB){/* FIXME: */$return['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL'].'/'.$r['id'];}
				echo json_encode($return);exit;
			case 'articleSaveText':
				if($articleOB){$_POST['_id_'] = $articleOB['id'];}
				if(!$articleOB){$_POST['articleAuthor'] = $GLOBALS['user']['userNick'];}
				$_POST['articleText'] = rawurldecode($_POST['articleText']);
				$_POST['articleText'] = str_replace(array(' class="MsoNormal"',' tabindex="0"',' class=""'),'',$_POST['articleText']);
				//FIXME: validar los estilos válidos
				/* DEPRECATED for compatibility */
				$_POST['articleText'] = preg_replace('/[\'\"][^\'\"]+(photos\/photo_[0-9]*\.jpeg)[\'\"]/','"$1"',$_POST['articleText']);
				/* Salvamos las imágenes en un formato algo más portable */
				$_POST['articleText'] = preg_replace('/<img[^>]*src=.[^\'\"]+article\/(image|file)\/[0-9]+\/([^\'\"]+).[^>]*>(<\/img>|)/','{%image:$2%}',$_POST['articleText']);
				$r = articles_save($_POST);
				if(isset($r['errorDescription'])){print_r($r);exit;}
				echo json_encode(array('errorCode'=>'0','data'=>$r));exit;
		}}

		if($articleOB){
			/* INI-conversion de fotos */
			$articleOB['articleText'] = preg_replace('/{%image:([^%]+)%}/','<img src="{%baseURL%}article/image/{%articleOB_id%}/$1"/>',$articleOB['articleText']);
			/** DEPRECATED **/
			$articleOB['articleText'] = preg_replace('/[\'\"](photos\/photo_[0-9]*\.jpeg)[\'\"]/','"{%baseURL%}article/$1"',$articleOB['articleText']);
			/* END-conversion de fotos */
			$articleOB['articleText'] = preg_replace('/style=.[^\'\"]+./','',$articleOB['articleText']);
		}

		/* INI-Detección de estilos */
		$cssFile = '../css/renderbase.css';
		if(file_exists($cssFile)){
			$blob = file_get_contents($cssFile);
			$r = preg_match_all('/p\.(?<pRules>[^.: \{]+)/',$blob,$m);
			$pRules = array_unique($m['pRules']);
			$s = '<ul><input name="paragraphStyle" value="" type="radio" selected="selected"/> Sin estilo';foreach($pRules as $pRule){
				$s .= '<li><input name="paragraphStyle" value="'.$pRule.'" type="radio"/> '.$pRule.'</li>';
			}
			$s .= '</ul>';
			$rep = array('style.list'=>$s);
			$TEMPLATE['edit.paragraph'] = common_loadSnippet('article/snippets/article.edit.paragraph',$rep);
		}
		/* END-Detección de estilos */

		$TEMPLATE['articleOB'] = $articleOB;
		if(!isset($TEMPLATE['articleOB']['articleText']) || empty($TEMPLATE['articleOB']['articleText'])){$TEMPLATE['articleOB']['articleText'] = '<p></p>';}
		common_loadScript('{%w.featherURL%}js/editor.js');
		common_loadScript('{%w.featherURL%}js/editor.signals.js');
		common_loadScript('{%w.featherURL%}js/upload.chain.js');
		common_loadScript('{%w.featherURL%}js/upload.chain.feather.js');
		common_loadScript('{%w.featherURL%}js/md5.js');
		common_loadStyle('{%w.featherURL%}css/renderbase.css');
		$TEMPLATE['BLOG_TITLE'] = ($articleOB) ? $articleOB['articleTitle'].' by '.(isset($articleOB['user']['userNick']) ? $articleOB['user']['userNick'] : 'dummy') : 'Nuevo artículo';
		$TEMPLATE['PAGE.MENU'] = common_loadSnippet('article/snippets/edit.menu');
		common_renderTemplate('article/edit');
	}

	function article_editm($aID = false){
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
$GLOBALS['COMMON']['BASE'] = 'base.markdown';
		include_once('api.articles.php');
		$articleOB = false;
		if($aID){do{
			$aID = preg_replace('/[^0-9]*/','',$aID);
			if(empty($aID)){$aID = false;break;}
			$articleOB = articles_getSingle('(id = '.$aID.')');
			if(!$articleOB){$aID = false;break;}
			$articleOB['user'] = article_author_getByAuthorAlias($articleOB['articleAuthor']);
			if(!$articleOB['user']){$articleOB['user'] = array('userNick'=>'dummy');}
			$articleOB['articleImages'] = article_image_getWhere('(articleID = '.$aID.')');
			$articleOB['articleImagesJSON'] = json_encode($articleOB['articleImages']);
		}while(false);}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'ajax.articleSaveProps':
				if($articleOB){$_POST['_id_'] = $articleOB['id'];}else{unset($_POST['_id_']);}
				$r = articles_save($_POST);if(isset($r['errorDescription'])){print_r($r);exit;}
				echo json_encode(array('errorCode'=>'0','data'=>$r));exit;
			case 'ajax.article.save.text':
				if($articleOB){$_POST['_id_'] = $articleOB['id'];}
				if(!$articleOB){$_POST['articleAuthor'] = $GLOBALS['user']['userNick'];}
				$_POST['articleText'] = rawurldecode($_POST['articleText']);
				$_POST['articleText'] = str_replace(array(' class="MsoNormal"',' tabindex="0"',' class=""'),'',$_POST['articleText']);
				//FIXME: validar los estilos válidos
				/* DEPRECATED for compatibility */
				$_POST['articleText'] = preg_replace('/[\'\"][^\'\"]+(photos\/photo_[0-9]*\.jpeg)[\'\"]/','"$1"',$_POST['articleText']);
				/* Salvamos las imágenes en un formato algo más portable */
//FIXME: salvar alts
				$_POST['articleText'] = preg_replace('/\!\[(?<imgAlt>[^\]]*)\]\(http:\/\/[^\'\"]+article\/image\/[0-9]+\/(?<imgID>[^\'\" \)]+)( .(?<imgTitle>[^\'\"]*).|)\)/','{%image:$2%}',$_POST['articleText']);
				$r = articles_save($_POST);
				if(isset($r['errorDescription'])){print_r($r);exit;}
				echo json_encode(array('errorCode'=>'0','data'=>$r));exit;
		}}

		if($articleOB){
			/* INI-conversion de fotos */
			$articleOB['articleText'] = preg_replace('/{%image:([^%]+)%}/','<img src="{%baseURL%}article/image/{%articleOB_id%}/$1"/>',$articleOB['articleText']);
			/** DEPRECATED **/
			$articleOB['articleText'] = preg_replace('/[\'\"](photos\/photo_[0-9]*\.jpeg)[\'\"]/','"{%baseURL%}article/$1"',$articleOB['articleText']);
			/* END-conversion de fotos */
			$articleOB['articleText'] = preg_replace('/style=.[^\'\"]+./','',$articleOB['articleText']);
		}

		$TEMPLATE['articleOB'] = $articleOB;
		if(!isset($TEMPLATE['articleOB']['articleText']) || empty($TEMPLATE['articleOB']['articleText'])){$TEMPLATE['articleOB']['articleText'] = '<p></p>';}
		common_loadScript('{%w.featherURL%}js/upload.chain.js');
		common_loadScript('{%w.featherURL%}js/md5.js');
		common_loadScript('{%w.featherURL%}js/coredown.js');
		$TEMPLATE['BLOG_CSS'][] = '{%baseURL%}css/renderbase.css';
		$TEMPLATE['BLOG_TITLE'] = ($articleOB) ? $articleOB['articleTitle'].' by '.$articleOB['user']['userNick'] : 'Nuevo artículo';
		$TEMPLATE['PAGE.MENU'] = common_loadSnippet('article/snippets/editm.menu');
		common_renderTemplate('article/editm');
	}

	function article_v($aID = false){
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		include_once('api.articles.php');
		$articleOB = false;
		$aID = preg_replace('/[^0-9]*/','',$aID);
		if(empty($aID)){$aID = false;break;}
		$articleOB = articles_getSingle('(id = '.$aID.')');
		if(!$articleOB){header('Location: '.$GLOBALS['baseURL'].'article/list');exit;}
		$articleOB['user'] = article_author_getByAuthorAlias($articleOB['articleAuthor']);
		if(!$articleOB['user']){$articleOB['user'] = array('userNick'=>'dummy');}
		$articleOB['articleImages'] = article_image_getWhere('(articleID = '.$aID.')');
		$articleOB['articleImagesJSON'] = json_encode($articleOB['articleImages']);
		/* Comentarios de artículos */
		$articleOB['comments'] = article_comment_getWhere('(commentChannel = '.$aID.')');

		$TEMPLATE['html.article.stream'] = presentation_article($articleOB);
		$TEMPLATE['html.comment.form'] = common_loadSnippet('article/snippets/comment.add');

		$TEMPLATE['articleOB'] = $articleOB;
		common_loadScript('{%w.featherURL%}js/coredown.js');
		common_renderTemplate('article/v');
	}

	function article_photos($photoName = false){
		/* for compatibility mode*/
		/** DEPRECATED **/
		if(!preg_match('/article\/edit\/(?<aID>[0-9]+)/',$_SERVER['HTTP_REFERER'],$m)){return false;}
		$aID = $m['aID'];
		$aID = preg_replace('/[^0-9]*/','',$aID);if(empty($aID)){return false;}
		include_once('api.articles.php');
		$articleOB = articles_getSingle('(id = '.$aID.')');if(!$articleOB){return false;}
		$time = strtotime($articleOB['articleDate']);
		$imagePath = $GLOBALS['api']['articles']['dir.db'].date('Y.m',$time).'/'.date('d',$time).'.'.$articleOB['articleName'].'/Photos/'.$photoName;
		if(!file_exists($imagePath)){return false;}
		$imgProp = @getimagesize($imagePath);
		header('Content-Type: '.$imgProp['mime']);
		readfile($imagePath);exit;
	}

	function article_image($aID = false,$imageName = false,$imageSize = false){
		include_once('api.articles.php');
		$imagePath = $GLOBALS['api']['articles']['dir.db'].$aID.'/images/'.$imageName.'/';
		if($imageSize){$imageSize = preg_replace('/[^0-9a-z\.]*/','',$imageSize);$imagePath .= $imageSize.'.jpeg';}
		else{$imagePath .= 'orig';}
		if(!file_exists($imagePath)){$imagePath = '../images/t.gif';}

		$imgProp = @getimagesize($imagePath);
		header('Content-Type: '.$imgProp['mime']);
		readfile($imagePath);exit;
	}
	function article_file($aID = false,$fileHash = false){
		include_once('api.articles.php');
		$aID = preg_replace('/[^0-9]*/','',$aID);
		$fileHash = preg_replace('/[^0-9a-zA-Z]*/','',$fileHash);
		$filePath = $GLOBALS['api']['articles']['dir.db'].$aID.'/files/'.$fileHash;if(!file_exists($filePath)){$r = article_file_deleteWhere('(articleID = '.$aID.' AND fileHash = \''.$fileHash.'\')');exit;}
		$fileOB = article_file_getSingle('(articleID = '.$aID.' AND fileHash = \''.$fileHash.'\')');

		header('Content-Type: '.$fileOB['fileMime']);
		readfile($filePath);exit;
	}

