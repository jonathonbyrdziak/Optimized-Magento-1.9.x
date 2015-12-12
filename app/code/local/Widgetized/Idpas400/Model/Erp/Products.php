<?php
/**
 * -What products are we pulling into the system
In AS400 speak, the file is SROPRG, in library ID2662AFB4.
The data structure map is. For reference I have included the entire Map.
The Data you will want is marked in RED
It is assumed that the ODBC connection name will be SROPRG in the example
Select PGPRDC,PGDESC,PGPCA1,PGPCA2,PGPCA3 FROM SROPRG WHERE PGPCA1='B4S' and 
PGFICC='N' and PGSTAT=' '

This should get only the B4School category items.
Category#2 PGPCA2 will have 'PAPER' or 'PLAST' for paper or plastic
Category#3 PGPCA3 will have 'JAN' or 'OFF' for Janitorial or Office 
PGFICC=N

  File Name ------ SROPRG                                                                                                           
  Library -------- ID2662AFB4                                                                                                       
  Format Descr ---                                                                                                                  
  Format Name ---- PRG                                                                                                              
  File Type ------ PF             Unique Keys - N                                                                                   
  Field Name Type  Start  Len  Dec    Description        
  PGOPNO      P      1      2  00     Option number                                                                                 
  PGDEID      A      3      8         Default ID                                                                                    
  PGSC01      A     11      1         Screen flag, screen 01                                                                        
  PGSC02      A     12      1         Screen flag, screen 02                                                                        
  PGSC03      A     13      1         Screen flag, screen 03                                                                        
  PGSTAT      A     14      1         Status                                                                                        
  PGPRDC      A     15     35         Item SKU number/ID                                                                                          
  PGDESC      A     50     50         Item description                                                                              
  PGPSNA      A    100     30         Internal name                                                                                 
  PGPTPE      A    130      5         Item type                                                                                     
  PGCSNO      A    135     18         Commodity code                                                                                
  PGPGRP      A    153      5         Item group                                                                                    
  PGAGRP      A    158      4         Account group                                                                                 
  PGPCA1      A    162      5         Item category 1 = Select where='B4S'                                                                              
  PGPCA2      A    167      5         Item category 2                                                                               
  PGPCA3      A    172      5         Item category 3                                                                               
  PGFICC      A    177      1         Fictitious item Y/N                                                                           
  PGTIXC      A    178      1         Time axis Y/N                                                                                 
  PGBTCC      A    179      1         Batch Y/N                                                                                     
  PGABCC      A    180      1         ABC code                                                                                      
  PGSENC      A    181      1         Serial number tracking Y/N                                                                    
  PGOHIS      A    182      1         Object history                                                                                
  PGCDAT      P    183      8  00     Creation date                                                                                 
  PGSTUN      A    188      5         Stock unit                                                                                    
  PGDECC      P    193      1  00     Number of decimals for amount                                                                 
  PGMSUP      A    194     11         Main supplier number                                                                          
  PGRESP      A    205     10         Responsible                                                                                   
  PGDSUN      A    215      5         Default sales unit                                                                            
  PGCOUN      A    220      4         Country                                                                                       
  PGBALC      A    224      1         Backlog Y/N                                                                                   
  PGSSTA      A    225      1         Add to sales statistics Y/N                                                                   
  PGOVDC      A    226      1         Valid for order discount Y/N                                                                  
  PGDSPC      A    227      1         Dispatch repricing Y/N                                                                        
  PGSTRT      A    228      2         Structure type                                                                                
  PGPSTA      A    230      1         Add to purchase statistics Y/N                                                                
  PGROPC      A    231      1         Order point calculation code                                                                  
  PGROPP      P    232     15  03     Order point                                                                                   
  PGEOQC      A    240      1         EOQ calculation code                                                                          
  PGEOQT      P    241     15  03     Order quantity                                                                                
  PGSECT      A    249      1         Safety stock type                                                                             
  PGSECS      P    250     15  03     Safety stock                                                                                  
  PGFORE      P    258     15  03     Annual forecast                                                                               
  PGLPCO      P    266     17  04     Last purchase cost                                                                            
  PGTLPC      P    275     17  04     Latest transit purchase cost                                                                  
  PGAPCO      P    284     17  04     Average purchase cost                                                                         
  PGTAPC      P    293     17  04     Average transit purchase cost                                                                 
  PGSTCO      P    302     17  04     Standard cost                                                                                 
  PGUFN1      P    311     17  03     User defined numeric field 1                                                                  
  PGUFN2      P    320     17  03     User defined numeric field 2                                                                  
  PGUFA1      A    329     15         User defined alpha field 1                                                                    
  PGUFA2      A    344     15         User defined alpha field 2                                                                    
  PGASAE      A    359      1         Allow sales after expiration date Y/N                                                         
  PGASBI      A    360      1         Allow to sale before incubation date Y/N                                                      
  PGADEL      A    361      1         Automatic delete of empty batch Y/N                                                           
  PGLSDY      P    362      4  00     Batch security days                                                                           
  PGSBDC      A    365      1         Sell by date  Y/N                                                                             
  PGSLSP      A    366      1         Split batch stat purchase Y/N                                                                 
  PGUDH1      A    367     20         Heading user defined date 1                                                                   
  PGUDH2      A    387     20         Heading user defined date 2                                                                   
  PGUDH3      A    407     20         Heading user defined date 3                                                                   
  PGUDH4      A    427     20         Heading user defined date 4                                                                   
  PGSLSS      A    447      1         Split batch stat sales Y/N                                                                    
  PGGTPE      A    448      5         Goods type                                                                                    
  PGSHPG      A    453      5         Shipment group                                                                                
  PGPCA4      A    458      5         Item category 4                                                                               
  PGPCA5      A    463      5         Item category 5                                                                               
  PGPCA6      A    468      5         Item category 6                                                                               
  PGSNFP      A    473     20         Serial number fixed part value                                                                
  PGSNNM      A    493     20         Serial number mask                                                                            
  PGSNNS      P    513      3  00     Serial number number series                                                                   
  PGLRDY      P    515      4  00     Batch retention days                                                                          
  PGCONG      A    518      5         Item Conformity Group                                                                         
  PGHCOD      A    523      5         Hazard/Handling Code                                                                          
  PGMSQR      P    528      5  02     Minimum Supplier Quality Rating                                                               
  PGUASO      A    531      1         Use approved supplier only                                                                    
  PGPDGR      A    532      5         Item Discount Group                                                                           
  PGCSTC      P    537     17  04     Calculated standard cost                                                                      
  PGEFDT      P    546      8  00     Effective date calculation cost                                                               
  PGMPSP      A    551     11         Main production supplier                                                                      
  PGTOOL      A    562      6         Tool identification number                                                                    
  PGPPRT      P    568      1  00     Production product type                                                                       
  PGPPNC      A    569      1         Production plan code                                                                          
  PGCCHA      A    570      1         Currency clause handling                                                                      
  PGSQGP      A    571      5         Supplier quotation group                                                                      
  PGPPGR      A    576      5         Item Price Group                                                                              
  PGPLAN      A    581     10         Planner                                                                                       
  PGHSTC      A    591      5         Handling status code                                                                          
  PGPVER      P    596      3  00     Item version                                                                                  
  PGDRNR      A    598     20         Drawing number                                                                                
  PGPRCL      A    618      1         Item class                                                                                    
  PGPRFA      A    619      5         Item family                                                                                   
  PGPRSE      A    624      5         Item sector                                                                                   
  PGAVCO      A    629      1         Availability check at order entry 1/2/3                                                       
  PGPLMD      A    630      1         Planning method                                                                               
  PGISUN      A    631      5         Issue unit                                                                                    
  PGSRVP      A    636      1         Service item Y/N                                                                              
  PGSPGR      A    637      5         Service item group                                                                            
  PGISET      A    642      5         Item segmentation type                                                                        
  PGIS01      A    647     35         Item segment 01                                                                               
  PGIS02      A    682     35         Item segment 02                                                                               
  PGIS03      A    717     35         Item segment 03                                                                               
  PGIS04      A    752     35         Item segment 04                                                                               
  PGIS05      A    787     35         Item segment 05                                                                               
  PGIS06      A    822     35         Item segment 06                                                                               
  PGISPR      A    857     35         Primary item segment                                                                          
  PGSCUN      A    892      5         Stock count unit                                                                              
  PGSTPU      P    897     17  04     Standard Purchase Price                                                                       
  PGPCDE      A    906      1         Profitability code                                                                            
  PGCTNB      P    907     19  04     Contribution                                                                                  
  PGPROP      P    917      5  02     Profit %                                                                                      
  PGCTNP      P    920      5  02     Contribution %                                                                                
  PGPCGR      A    923      5         Item commission group                                                                         
  PGCDYN      A    928      1         Cash discount Y/N                                                                             
  PGTOYN      A    929      1         Turnover amount Y/N                                                                           
  PGMSDS      A    930     10         MSDS                                                                                          
  PGSATX      A    940      1         Sales tax Y/N                                                                                 
  PGSTCA      A    941     15         Sales tax category                                                                            
  PGSTCL      P    956      3  00     Sales tax class                                                                               
  PGCFCO      A    958      2         Created from company                                                                          
  PGCRTI      P    960      6  00     Creation time                                                                                 
  PGCTYP      P    964      1  00     Cost type 1/2/3/4                                                                             
  PGAVMT      A    965      1         Availability check BOM Y/N                                                                    
  PGAVOP      A    966      1         Availability check BOR Y/N                                                                    
  PGAVSP      A    967      1         Availability check SOP Y/N                                                                    
  PGBATM      A    968     20         Batch mask                                                                                    
  PGCSPU      P    988     17  04     New standard purchase price                                                                   
  PGWHCD      A    997     10         Sourcing policy                                                                               
  PGASRC      A   1007      1         Auto source Y/N                                                                               
  PGDESG      A   1008      1         Display/entry segmentation                                                                    
  PGSTCS      P   1009     17  04     Standard cost sales                                                                           
  PGMCWH      A   1018      3         Main cost warehouse                                                                           
  PGMGCB      P   1021      1  00     Margin cost basis                                                                             
  PGCXXPTP    A   1022      1         Exchange item type                                                                            
  PGCXIATP    A   1023      1         Inventory accounting type                                                                     
  PGCXXGRP    A   1024     13         Exchange item group                                                                           
  PGCXXCPC    A   1037     35         Clean exchange item code    
 */
class Widgetized_Idpas400_Model_Erp_Products extends Widgetized_Idpas400_Model_Abstract {
    
    /**
     *
     * @var type 
     */
    protected $_tableName = 'SROPRG';
    
    /**
     *
     * @var type 
     */
    protected $_id = 'PGPRDC';
    
    protected $_className = 'idpas400/erp_products';

    /**
     *
     * @var type 
     */
    public $_mapping = array(
        'PGSTAT' => 'status',
        'PGPRDC' => 'sku',
        'PGPSNA' => 'name',
        'PGCOUN' => 'country_of_manufacture',
        'PGLPCO' => 'cost',
        // PGSATX: Sales tax Y/N   
        'PGSATX' => 'tax_yn',
        //PGSTCA      A    941     15         Sales tax category
        'PGSTCA' => 'tax_category',
        //PGSTCL      P    956      3  00     Sales tax class       
//        'PGSTCL' => 'tax_class',
        'PGPCA1' => 'category_1',
        'PGPCA2' => 'category_2',
        'PGPCA3' => 'category_3',
        'PGPCA4' => 'category_4',
        'PGPCA5' => 'category_5',
        'PGPCA6' => 'category_6',
        'PGUFA1' => 'case'
    );
    
    /**
     * 
     * @return type
     */
    public function getCollection() {
        $records = Mage::getSingleton('idpas400/db')
                ->fetch_array("SELECT $this->_id as ID"
                . " FROM $this->_tableName"
                . " WHERE PGPCA1 = 'B4S'");

        foreach ($records as $key => $record) {
            $instance = Mage::getModel('idpas400/erp_products')->load(trim($record['ID']));
            $records[$key] = $instance;
        }
        return $records;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function load( $id ) {
        parent::load($id);
        
        //WEIGHT
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_row("SELECT PJGWGT FROM SROPRU"
                . " WHERE PJPRDC='{$this->getData('sku')}'"
                . " AND PJUNIT='CS'");
        if (isset($data['PJGWGT'])) $this->setData('weight', trim($data['PJGWGT']));
        
        //PRICING
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_row("SELECT PPRPRV,PPSALP FROM SROPPS"
                . " WHERE PPPRDC='{$this->getData('sku')}'"
                . " AND PPCCUR='USD' AND PPUNIT='CS' AND PPPRIL='01'");
                
        //PPRPRV is the Retail price, Numeric (17,4)
        if (isset($data['PPRPRV'])) $this->setData('msrp', trim($data['PPRPRV'])); 
        //PPRPRV is the Retail price, Numeric (17,4)
        if (isset($data['PPSALP'])) $this->setData('price', trim($data['PPSALP'])); 
        
        //INVENTORY
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_row("SELECT SRSTHQ FROM SROSRO"
                . " WHERE SRPRDC='{$this->getData('sku')}'"
                . " AND SRSROM='B4S'");
        if (isset($data['SRSTHQ'])) $this->setData('qty', trim($data['SRSTHQ']));
        
        //DESCRIPTION
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_array("SELECT XTTX50 FROM SROPLX"
                . " WHERE XTPRDC='{$this->getData('sku')}'"
                . " AND XTLANG='EN'"
                . " ORDER BY XTTLIN ASC");
                
        $description = '';
        foreach((array)$data as $d) $description .= " ".trim($d['XTTX50']);
        $this->setData('description', trim($description));
        $this->setData('short_description', trim($description));

        //SPECS
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_array("SELECT XTTX50 FROM SROPLX"
                . " WHERE XTPRDC='{$this->getData('sku')}'"
                . " AND XTLANG='SP'"
                . " ORDER BY XTTLIN ASC");
                
        $specs = '';
        foreach((array)$data as $d) $specs .= " ".trim($d['XTTX50']);
        if ($specs) {
            $this->setData('description', trim($specs));
        }
        
        // get product weight from Z1OB4SKUZ
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_row("SELECT SZPRDC,SZSROM,SZMOTC,SZWGHT,SZSHCO,SZTOPR FROM Z1OB4SKUZ"
                . " WHERE SZPRDC='{$this->getData('sku')}'"
                . " AND SZSROM='B4S' AND SZMOTC='UPG'");
        if (isset($data['SZWGHT'])) $this->setData('weight', trim($data['SZWGHT']));
        
        
        //IMAGES
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_array("SELECT PXTX50"
                . " FROM SROPRX"
                . " WHERE PXPRDC='{$this->getData('sku')}'"
                . " AND PXLANG='EN'"
                . " AND SUBSTRING(PXTX50,1,1)='/'");
        $images = array();
        foreach ((array)$data as $d) $images[] = trim($d['PXTX50']);
        $this->setData('images', $images);
        
        return $this;
    }
    
    protected $url = '';
    protected $path = 'media/';
    
    /**
     * 
     * @param string $inPath
     * @param string $outPath
     * @return type
     */
    function saveImage($inPath,$outPath) {
        return ; //@todo test this method
        
        $inPath = $this->url.$inPath;
        $outPath = $this->path.$outPath; // where the image is saved
        
        //Download images from remote server
        $in = fopen($inPath, "rb");
        $out = fopen($outPath, "wb");
        while ($chunk = fread($in, 8192)) {
            fwrite($out, $chunk, 8192);
        }
        fclose($in);
        fclose($out);
    }

}
