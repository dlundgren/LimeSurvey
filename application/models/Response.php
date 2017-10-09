<?php
    /**
     * Relations
     * @property Token $token
     * @property Survey $survey
     */
    abstract class Response extends Dynamic
    {
        public function beforeDelete() {
            if (parent::beforeDelete())
            {
                $this->deleteFiles();
                return true;
            }
            return false;
        }
        /**
         *
         * @param mixed $className Either the classname or the survey id.
         * @return Response
         */
        public static function model($className = null) {
            /** @var self $model */
            $model =parent::model($className);
            return $model;
        }
        /**
         *
         * @param int $surveyId
         * @param string $scenario
         * @return Response Description
         */
        public static function create($surveyId, $scenario = 'insert') {
            return parent::create($surveyId, $scenario);
        }

        /**
         * Get all files related to this response and (optionally) question ID.
         *
         * @param integer $sQID The question ID - optional - Default 0
         * @return array
         */
        public function getFiles($sQID=0)
        {
            $survey = Survey::model()->findByPk($this->dynamicId);
            $questions = Question::model()->findAllByAttributes(array('sid' => $this->dynamicId,'type' => '|','language'=>$survey->language));
            $files = array();
            foreach ($questions as $question) {
                $field = "{$question->sid}X{$question->gid}X{$question->qid}";
                $data = json_decode(stripslashes($this->getAttribute($field)), true);
                if (is_array($data)) {
                    $files = array_merge($files, $data);
                }
            }
            return $files;
        }
        public function deleteFiles()
        {
            $uploaddir = Yii::app()->getConfig('uploaddir') ."/surveys/{$this->dynamicId}/files/";
            foreach ($this->getFiles() as $fileInfo)
            {
                @unlink($uploaddir . basename($fileInfo['filename']));
            }
        }
        public function delete($deleteFiles = false) {
            if ($deleteFiles) {
                $this->deleteFiles();
            }
            return parent::delete();
        }
        public function relations()
        {
            $result = array(
                'token' => array(self::BELONGS_TO, 'Token_' . $this->dynamicId, array('token' => 'token')),
                'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}" )
            );
            return $result;
        }
        public function tableName()
        {
            return '{{survey_' . $this->dynamicId . '}}';
        }
        public function getSurveyId() {
            return $this->dynamicId;
        }
        public function browse(){
        }
        public function search(){

        }
    }
