<?php
class Source extends AppModel{
	public $belongsTo = array(
		'Lecture' => array(
			'className' => 'Lecture',
			'foreignKey' => 'lecture_id'
			)
		);

	public $validate = array(
		'filename' => array(
			// http://book.cakephp.org/2.0/en/models/data-validation.html#Validation::uploadError
			'uploadError' => array(
				'rule' => 'uploadError',
				'message' => 'Something went wrong with the file upload',
				'required' => FALSE,
				'allowEmpty' => TRUE,
			),
			// http://book.cakephp.org/2.0/en/models/data-validation.html#Validation::mimeType
			'mimeType' => array(
				'rule' => array('mimeType', array('application/msword','image/gif','image/png','image/jpg','image/jpeg','application/pdf','application/msword','text/tab-separated-values',
					'audio/mpeg', 'audio/x-mpeg', 'audio/mp3', 'audio/x-mp3', 'audio/mpeg3', 'audio/x-mpeg3', 'audio/mpg', 'audio/x-mpg', 'audio/x-mpegaudio',
					'video/mpeg','video/x-mpeg','video/mp4','video/mp4v-es', 'audio/mp4')),
				'message' => 'Invalid file, only images allowed',
				'required' => FALSE,
				'allowEmpty' => TRUE,
			),
			// custom callback to deal with the file upload
			'processUpload' => array(
				'rule' => 'processUpload',
				'message' => 'Something went wrong processing your file',
				'required' => FALSE,
				'allowEmpty' => TRUE,
				'last' => TRUE,
			)
		)
	);

	/**
	 * Upload Directory relative to WWW_ROOT
	 * @param string
	 */
	public $uploadDir = 'uploads';

	/**
	 * Before Validation Callback
	 * @param array $options
	 * @return boolean
	 */
	public function beforeValidate($options = array()) {
		// ignore empty file - causes issues with form validation when file is empty and optional
		if (!empty($this->data[$this->alias]['filename']['error']) && $this->data[$this->alias]['filename']['error']==4 && $this->data[$this->alias]['filename']['size']==0) {
			unset($this->data[$this->alias]['filename']);
		}

		return parent::beforeValidate($options);
	}

	/**
	 * Before Save Callback
	 * @param array $options
	 * @return boolean
	 */
	public function beforeSave($options = array()) {
		// a file has been uploaded so grab the filepath
		if (!empty($this->data[$this->alias]['filepath'])) {
			$this->data[$this->alias]['filename'] = $this->data[$this->alias]['filepath'];
		}

		return parent::beforeSave($options);
	}

	/**
	 * Process the Upload
	 * @param array $check
	 * @return boolean
	 */
	public function processUpload($check=array()) {
		// deal with uploaded file
		if (!empty($check['filename']['tmp_name'])) {

			// check file is uploaded
			if (!is_uploaded_file($check['filename']['tmp_name'])) {
				return FALSE;
			}

			// build full filename
			$filename = WWW_ROOT . $this->uploadDir . DS . Inflector::slug(pathinfo($check['filename']['name'], PATHINFO_FILENAME)).'.'.pathinfo($check['filename']['name'], PATHINFO_EXTENSION);

			// @todo check for duplicate filename

			// try moving file
			if (!move_uploaded_file($check['filename']['tmp_name'], $filename)) {
				return FALSE;

			// file successfully uploaded
			} else {
				// save the file path relative from WWW_ROOT e.g. uploads/example_filename.jpg
				$this->data[$this->alias]['filepath'] = str_replace(DS, "/", str_replace(WWW_ROOT, "", $filename) );
			}
		}

		return TRUE;
	}
}

?>