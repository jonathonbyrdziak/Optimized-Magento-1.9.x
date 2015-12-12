<?php

/**
 * 
 * -Schools 
 * In Enterprise we now have a new file for the B4School Public/private School 
 * Database. This file contains a list of schools in the state of California 
 * format. The file is Z1OSPSD, in library ID2662AFB4. 
 * 
 * The file contains a full list of all the schools that the state has collected
 * information on. The map is the same as the states xls data. 
 * 
 * The Data you will want is marked in RED
 File Name........ Z1OSPSD                                                      
 Library........   ID2662AFB4                                                 
 Format Descr.....                                                              
 Format Name...... Z1B4SPSD                                                     
 File Type........ PF            Unique Keys - N                                
 Field Name FMT Start Lngth Dec Key Field Description                           
 PSCDSC      A      1    14     K01 CDSCODE                                     
 PSNCESD     A     15     7         NCESDIST                                    
 PSNCESS     A     22     5         NCESSCHOOL                                  
 PSSTSTYP    A     27    50         STATUS TYPE                                 
 PSCOUNTY    A     77    15         COUNTY NAME                                 
 PSDISTRI    A     92    90         DISTRICT NAME                               
 PSSCHOOL    A    182    90         SCHOOL NAME                                 
 PSADDR1     A    272   211         ADDRESS1 UNABBREVIATED                      
 PSADDR2     A    483   201         ADDRESS2                                    
 PSADDRCT    A    684    25         CITY                                        
 PSADDRZP    A    709    10         ZIP                                         
 PSADDRST    A    719     2         STATE                                       
 PSMADDR1    A    721   211         ADDRESS1 MAIL                               
 PSMADDR2    A    932   201         ADDRESS2 MAIL                               
 PSMADDRCT   A   1133    25         CITY MAIL                                   
 PSMADDRZP   A   1158    10         ZIP MAIL                                    
 PSMADDRST   A   1168     2         STATE MAIL                                  
 PSPHONE     A   1170    14         PHONE                                       
 PSPHOEXT    A   1184     6         PHONE EXTENSION                             
 PSWEBSITE   A   1190   100         SCHOOL WEB SITE                             
 PSDTOPEN    A   1290    10         DATE OPENED                                 
 PSDTCLOS    A   1300    10         DATE CLOSED                                 
 PSCHARTR    A   1310     1         CHARTER SCHOOL                              
 PSCHART#    A   1311     4         CHARTER SCHOOL NUMBER                       
 PSFUNDTY    A   1315    25         FUNDING TYPE                                
 PSDSTOCD    A   1340     2         DISTRICT OWNERSHIP CODE                     
 PSDSTOTY    A   1342    50         DISTRICT CODE TYPE                          
 PSSCHOCD    A   1392     2         SCHOOL OWNERSHIP CODE                       
 PSSCHOTY    A   1394    50         SCHOOL CODE TYPE                            
 PSEDOPCD    A   1444    20         EDUCATIONAL OPTION CODE                     
 PSEDOPNM    A   1464   100         EDUCATIONAL OPTION NAME                     
 PSEILCD     A   1564    50         EDUCATIONAL INSTRUCTION CODE                
 PSEILNM     A   1614    50         EDUCATIONAL INSTRUCTION NAME                
 PSGSO       A   1664   101         GRADE SPAN OFFERED                          
 PSGSS       A   1765   101         GRADE SPAN SERVED                           
 PSGPSLAT    A   1866    10         GPS LATITUDE                                
 PSGPSLON    A   1876    10         GPS LONGITUDE                               
 PSADMFN1    A   1886    20         1-FIRST NAME ADMIN/PRINCIPAL                
 PSADMLN1    A   1906    40         1-LAST NAME ADMIN/PRINCIPAL                 
 PSADMEM1    A   1946    50         1-EMAIL ADMIN/PRINCIPAL                     
 PSADMFN2    A   1996    20         2-FIRST NAME ADMIN/PRINCIPAL                
 PSADMLN2    A   2016    40         2-LAST NAME ADMIN/PRINCIPAL                 
 PSADMEM2    A   2056    50         2-EMAIL ADMIN/PRINCIPAL                     
 PSADMFN3    A   2106    20         3-FIRST NAME ADMIN/PRINCIPAL                
 PSADMLN3    A   2126    40         3-LAST NAME ADMIN/PRINCIPAL                 
 PSADMEM3    A   2166    50         3-EMAIL ADMIN/PRINCIPAL                     
 PSDTLSUP    A   2216    10         DATE LAST UPDATED    
 */
class Widgetized_Idpas400_Model_Erp_Schools 
    extends Widgetized_Idpas400_Model_Abstract {

    /**
     *
     * @var type 
     */
    protected $_tableName = 'Z1OSPSD';
    
    /**
     *
     * @var type 
     */
    protected $_id = 'PSCDSC';

    /**
     *
     * @var type 
     */
    protected $_mapping = array(
        'PSCDSC' => 'entity_id',
    );
    
    protected $_className = 'idpas400/erp_schools';
}
