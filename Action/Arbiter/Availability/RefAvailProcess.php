<?php
namespace Zayso\ArbiterBundle\Component;

class RefAvailRow
{
    function set($data)
    {
        $this->name   = trim($data[0]);
        $this->rank   = (int)trim($data[1]);
        $this->games  = (int)trim($data[2]);
        $this->city   = trim($data[3]);
        $this->avail  = trim($data[5]);
        $this->phone1 = trim($data[8]);
        $this->phone2 = trim($data[10]);
        $this->pageCount = (int)trim($data[11]);
    }
}
class RefAvailItem
{
    public $avails = array();

    function __construct($row)
    {
        $this->name   = $row->name;
        $this->rank   = $row->rank;
        $this->games  = $row->games;
        $this->city   = $row->city;
        $this->phone1 = $row->phone1;
        $this->phone2 = $row->phone2;

        $this->avails = array();
    }
    function addAvail($date,$avail)
    {
        if ($avail) $this->avails[$date][] = $avail;
    }
    function getAvail($date)
    {
        if (isset($this->avails[$date])) return $this->avails[$date];
        return array();
    }
    function addName($name)
    {
        if ($name) $this->name .= ' ' . $name;
    }
}
class RefAvailProcess
{
    protected $date  = null;
    protected $dates = array();

    protected $item  = null;
    protected $items = array();

    function addDate($date)
    {
        $this->dates[] = $date;
        $this->date    = $date;
    }
    function addItem($item)
    {
        // See if new record
        if (!isset($this->items[$item->name]))
        {
            $this->items[$item->name] = $item;
            return;
        }
        // Merge into existing record
        $itemx = $this->items[$item->name];
        foreach($item->avails as $date => $avails)
        {
            foreach($avails as $avail)
            {
                $itemx->addAvail($date,$avail);
            }
        }
        // Suppose could destroy item here but won't
    }
    function processRow($row)
    {
        // Is it a date?
        // MSSL Avail Availability for Monday, 1/24/2011
        $dateKey = 'Availability for';
        if (strstr($row->name,$dateKey) !== FALSE)
        {
            $data = explode(',',$row->name);
            $data = explode('/',trim($data[1]));

            $month = $data[0]; if (strlen($month) < 2) $month = '0' . $month;
            $day   = $data[1]; if (strlen($day)   < 2) $day   = '0' . $day;
            $year  = $data[2];

            $date = $year . $month . $day;
            $this->addDate($date);

            // echo "Date {$date}\n";
            return;
        }

        // Is it a new item?
        if ($row->rank)
        {
            // Add the previous item
            if ($this->item) $this->addItem($this->item);

            // Make a new one
            $this->item = new RefAvailItem($row);

            // Add the availability
            $this->item->addAvail($this->date,$row->avail);

            // Done
            // echo "{$row->name} {$row->rank}\n";
            return;
        }
        // Empty fill row
        if (!$row->name && !$row->avail)
        {
            if ($this->item) $this->addItem($this->item);
            $this->item = NULL;
            return;
        }
        // Footer line
        if ($row->pageCount)
        {
            // echo "{$row->name} {$row->pageCount}\n";
            if ($this->item) $this->addItem($this->item);
            $this->item = NULL;
            return;
        }
        // Could be additional avails or middle name
        if (!$this->item) return;

        $this->item->addName ($row->name);
        $this->item->addAvail($this->date,$row->avail);
    }
    function importCSV($fileName)
    {
        $file = fopen($fileName,'rt');
        $row = new RefAvailRow();
        while (($line = fgetcsv($file)))
        {
            $row->set($line);

            $this->processRow($row);
        }
        fclose($file);
    }
    function exportCSV($fileName = null)
    {
        $file = fopen($fileName,'w');

        $row = array('Name','Rank','City');
        foreach($this->dates as $date)
        {
            $ts = mktime(0,0,0,substr($date,4,2),substr($date,6,2),substr($date,0,4));
            $row[] = date('D, d-M-Y',$ts);
        }
        fputcsv($file,$row,',','"');

        $count = count($this->items);

        foreach($this->items as $item)
        {
            //print_r($item);
            $row = array($item->name,$item->rank,$item->city);
            foreach($this->dates as $date)
            {
                $row[] = implode("\n",$item->getAvail($date));
            }
            fputcsv($file,$row);
            //die();
        }
        fclose($file);
    }
}
?>
