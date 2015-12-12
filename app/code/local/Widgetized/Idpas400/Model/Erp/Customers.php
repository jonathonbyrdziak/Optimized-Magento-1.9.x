<?php
/**
 * -Customers
 * In Business partner file contains both customers and suppliers, the file is 
 * SRONAM, in library ID2662AFB4. There are other files associated with the 
 * customer for addresses etc, these will be covered later.
 * 
The data structure map is. For reference I have included the entire Map.
The Data you will want is marked in RED
Select NANUM,NATYPP,NANAME FROM SRONAM WHERE NANUM='1234567890' AND NATYPP=1
This should get a customer type BP record for 1234567890.
 File Name........ SRONAM                                                       
 Library........   ID2662AFB4                                                 
 Format Descr.....                                                              
 Format Name...... NAM                                                          
 File Type........ PF            Unique Keys - N                                
 Field Name FMT Start Lngth Dec Key Field Description                           
 NAOPNO      P      1     2  00     Option number                               
 NADEID      A      3     8         Default ID                                  
 NASTAT      A     11     1         Status                                      
 NANUM       A     12    11         Customer/Supplier number                    
 NANSNO      A     23    11         Alias for customer/supplier number          
 NATYPP      P     34     1  00     Type                                        
 NANAME      A     35    30         Name                                        
 NAADR1      A     65    35         Address line 1                              
 NAADR2      A    100    35         Address line 2                              
 NAADR3      A    135    35         Address line 3                              
 NAADR4      A    170    35         Address line 4    (CITY)                          
 NAPOCD      A    205    16         Postal code       (ST ZIPCD-PLUS)                         
 NANSNA      A    221    20         Internal name                               
 NACREG      A    241    16         Company registration number                 
 NATREG      A    257    16         VAT registration number                     
 NADUMM      A    273     1         Sundry number                               
 NACOUN      A    274     4         Country                                     
 NAAREA      A    278     3         Area                                        
 NALANG      A    281     3         Language                                    
 NANCA1      A    284     6         Business partner category 1                 
 NANCA2      A    290     6         Business partner category 2                 
 NANCA3      A    296     6         Business partner category 3                 
 NACRDT      P    302     8  00     Creation date                               
 NAIORD      A    307     1         Internal order                              
 NAPROD      A    308     1         Production order                            
 NAINNF      P    309    17  03     Invoiced amount not posted to Financial     
 NACNTY      A    318     5         County code                                 
 NASPCD      A    323     2         State/Province Code                         
 NATAXJ      A    325    12         Tax Jurisdiction Code                       
 NANANN      P    337    11  00     Customer/Supplier number numeric            
 NAMDCN      A    343     1         MDC Business partner Y/N                    
 NAPCDE      A    344     1         Profitability code                          
 NACTNB      P    345    19  04     Contribution                                
 NAPROP      P    355     5  02     Profit %                                    
 NACTNP      P    358     5  02     Contribution %                              
 NAARHA      A    361    10         A/R Main Salesman/handler                   
 NAAPHA      A    371    10         A/P Handler                                 
 NAISVC      A    381     1         Internal service Y/N                        
 NANCA4      A    382     6         Business partner category 4                 
 NANCA5      A    388     6         Business partner category 5                 
 NANCA6      A    394     6         Business partner category 6                 
 NAPCTY      A    400     5         Print control code   
 */
class Widgetized_Idpas400_Model_Erp_Customers 
    extends Widgetized_Idpas400_Model_Abstract {

    /**
     *
     * @var type 
     */
    protected $_tableName = 'SRONAM';
    
    /**
     *
     * @var type 
     */
    protected $_id = 'NADEID';

    /**
     *
     * @var type 
     */
    protected $_mapping = array(
        'NADEID' => 'entity_id',
        'NANAME' => 'firstname',
        'PGDESC' => 'description',
        'PGPSNA' => 'name',
        'PGCOUN' => 'country_of_manufacture',
    );
    
    protected $_className = 'idpas400/erp_customers';
    
}
