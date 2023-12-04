<?php
class Certificate
{
    function __construct($data)
    {
        $this->data = $data;
    }
    private function getCertLocation($cert){
        return '/var/www/certificates/' . $cert .'/'. $this->data['eid'] . '_'.$cert.'.pdf';

    }
    private function getCertLocationPHP($cert){
        return '/var/www/certificates/' . $cert .'/'. $this->data['eid'] . '_'.$cert.'.php';

    }

    private function getButton($certName){
        return '<button class="btn" target="_blank" onclick="window.open(\'/download/cert/'.$certName.'?user='.$this->data['eid']."')\"><span class='material-icons'>open_in_new</span></button>";
    }

    public function displayCertificate(){
        ?>
            <table class='userCredentials'  align="center">
                <thead>
                    <tr>
                        <th>Building Security Awareness</th>
                        <th>MATLAB Training for Building Access</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php 
                        echo '<td>Completion date: '.$this->data['training_date1'] . '</td>';
                        echo '<td>Completion date: '.$this->data['training_date2'] . '</td>';
                        ?>
                    </tr>
                    <tr>
                        <td>Status:
                        <?php 
                            echo ($this->data['status2']) ? '<span class="material-icons colorGreen">done</span>' : '<span class="material-icons colorRed">close</span>';
                            echo '</td><td>Status: ';
                            echo ($this->data['status2']) ? '<span class="material-icons colorGreen">done</span>' : '<span class="material-icons colorRed">close</span>';
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                        echo '<td>';
                        if (file_exists($this->getCertLocation('1')) || file_exists($this->getCertLocationPHP('2'))) {
                            $_SESSION['staus1'] = $this->getCertLocation('1');
                            echo $this->getButton('1');
                        }
                        else {
                            echo 'File not found - check if user added from exel';
                        }
                        echo '</td><td>';
                        if (file_exists($this->getCertLocation('2')) || file_exists($this->getCertLocationPHP('2'))) {
                            $_SESSION['staus2'] = $this->getCertLocation('2');
                            echo $this->getButton('2');
                        }
                        else {
                            echo 'File not found - check if user added from exel';
                        }
                        echo '</td>';
                        ?>
                    </tr>
                </tbody>
            </table>
        <?php
    }
}