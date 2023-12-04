<?php

class ActiveRequests
{
    function __construct($data)
    {
        $this->status = $data['status'];

        $this->labs = [
            'building3' => $data['building3'],
            'building2' => $data['building2'],
            'building1' => $data['building1'],
            'room4' => $data['room4'],
            'room3' => $data['room3'],
            'room2' => $data['room2'],
            'room1' => $data['room1'],
        ];
        $this->justifications = [
            'room4_justification' => $data['room4_justification'],
            'room3_justification' => $data['room3_justification'],
            'room2_justification' => $data['room2_justification'],
            'room1_justification' => $data['room1_justification'],
        ];
    }

    public function displayActiveRequest($showJustification)
    {
        if($this->status != 0 && $this->status != 4){
            ?>
            <table class='userCredentials' align="center">
                <thead>
                    <tr>
                        <th colspan='100'>
                            Current access rights to laboratories
                        </th>
                    </tr>
                    <tr>
                        <th>building3</th>
                        <th>building2</th>
                        <th>building1</th>
                        <th>room4</th>
                        <th>room3</th>
                        <th>room2</th>
                        <th>room1</th>
                    </tr>
                </thead>
                <tbody>
                    <tr> 
                        <?php
                            foreach($this->labs as $lab){
                                echo '<td>';
                                echo ($lab) ? '<span class="material-icons colorGreen">done</span>' : '<span class="material-icons colorRed">close</span>';
                                echo '</td>';
                            }
                        ?>
                    </tr>
                    <?php if($showJustification): ?>
                    <tr>
                        <td colspan='3'></td>
                        <?php
                            foreach($this->justifications as $justification){
                                if(empty($justification)){
                                    echo '<td><span class="material-icons colorGray">remove</span></td>';
                                } else {
                                    echo '<td>'.$justification . '</td>';
                                }
                            }
                        ?>
                    </tr>
                    <?php endif ?>
                </tbody>
            </table>
            <?php
        }
    }
}

?>