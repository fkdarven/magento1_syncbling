<?php
class Darven_SyncBling_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action{
    protected function _initAction()
    {

        $this->loadLayout()->_setActiveMenu('syncbling/adminhtml_report');
        return $this;
    }

    public function indexAction()
    {

        $this->_initAction();
        //$this->renderLayout();

        //$block = $this->getLayout()->createBlock('Mage_Core_Block_Template','adminhtml_report', array('template' => 'darven/syncbling/report.phtml'));
        //$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();

    }
    public function exportCsvAction()
    {

        $filename = "var/log/syncBling.log";

        if (file_exists($filename))
        {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            exit;
        }
    }

}