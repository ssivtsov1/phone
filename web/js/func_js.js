// Функция перекодирования из разных раскладок клавиатуры
// а также изменение регистра        
function recode_txt(p,elem,mode){   
//   Аргументы:
//   p - строка
//   elem - HTML Элемент в который нужно поместить результат (первый символ либо .(класс), либо #(если id)
//   mode - режим:
//   1 - Рус.
//   2 - Укр.
//   3 - Лат.
//   4 - Все большие
//   5 - Все маленькие

    $(".rmenu").hide();
        
    var y,i,c,nc='',phrase = '',flag = 0;
        if(!mode){
             //phrase = localStorage.getItem("origin_val_"+elem.substr(1));
             
        }
        y = p.length;
        for(i=0;i<y;i++)
        {
            if((y-i)>0) c1 = p.substr(i+1,1);
            c = p.substr(i,1);
            var symb = c.search(/\w/);
           
            if((symb==-1) && (c1==' ')) flag = 1;
            else flag = 0;
            
            if(mode==4){
                // Все большие
                phrase=p.toUpperCase()
            }
            if(mode==5){
                // Все маленькие
                phrase=p.toLowerCase()
            }
            if(mode==1){
                // -> Рус
            switch(c) {
                case 'q':  nc = 'й';
                    break;
                case 'w':  nc = 'ц';
                    break;
                case 'e':  nc = 'у';
                    break;
                case 'r':  nc = 'к';
                    break;
                case 't':  nc = 'е';
                    break;

                case 'y':  nc = 'н';
                    break;
                case 'u':  nc = 'г';
                    break;
                case 'i':  nc = 'ш';
                    break;
                case 'o':  nc = 'щ';
                    break;
                case 'p':  nc = 'з';
                    break;

                case '[':  nc = 'х';
                    break;
                case ']':  nc = 'ъ';
                    break;
                case 'a':  nc = 'ф';
                    break;
                case 's':  nc = 'ы';
                    break;
                case 'd':  nc = 'в';
                    break;

                case 'f':  nc = 'а';
                    break;
                case 'g':  nc = 'п';
                    break;
                case 'h':  nc = 'р';
                    break;
                case 'j':  nc = 'о';
                    break;
                case 'k':  nc = 'л';
                    break;

                case 'l':  nc = 'д';
                    break;
                case ';':  nc = 'ж';
                    break;
                case "'":  nc = 'э';
                    break;
                case 'z':  nc = 'я';
                    break;
                case 'x':  nc = 'ч';
                    break;

                case 'c':  nc = 'с';
                    break;
                case 'v':  nc = 'м';
                    break;
                case "b":  nc = 'и';
                    break;
                case 'n':  nc = 'т';
                    break;
                case 'm':  nc = 'ь';
                    break;
                case ',':  nc = 'б';
                    if(flag) nc = c;
                    break;
                case '.':  nc = 'ю';
                    if(flag) nc = c;
                    break;
                case 'і':  nc = 'ы';
                    break; 
                case 'ї':  nc = 'ъ';
                    break;  
                case 'є':  nc = 'э';
                    break; 

                
                
                case 'Q':  nc = 'Й';
                    break;
                case 'W':  nc = 'Ц';
                    break;
                case 'E':  nc = 'У';
                    break;
                case 'R':  nc = 'К';
                    break;
                case 'T':  nc = 'Е';
                    break;

                case 'Y':  nc = 'Н';
                    break;
                case 'U':  nc = 'Г';
                    break;
                case 'I':  nc = 'Ш';
                    break;
                case 'O':  nc = 'Щ';
                    break;
                case 'P':  nc = 'З';
                    break;

                case '[':  nc = 'Х';
                    break;
                case ']':  nc = 'Ъ';
                    break;
                case 'A':  nc = 'Ф';
                    break;
                case 'S':  nc = 'Ы';
                    break;
                case 'D':  nc = 'В';
                    break;

                case 'F':  nc = 'А';
                    break;
                case 'G':  nc = 'П';
                    break;
                case 'H':  nc = 'Р';
                    break;
                case 'J':  nc = 'О';
                    break;
                case 'K':  nc = 'Л';
                    break;

                case 'L':  nc = 'Д';
                    break;
                case ';':  nc = 'Ж';
                    break;
                case "'":  nc = 'Э';
                    break;
                case 'Z':  nc = 'Я';
                    break;
                case 'X':  nc = 'Ч';
                    break;

                case 'C':  nc = 'С';
                    break;
                case 'V':  nc = 'М';
                    break;
                case "B":  nc = 'И';
                    break;
                case 'N':  nc = 'Т';
                    break;
                case 'M':  nc = 'Ь';
                    break;
                case ',':  nc = 'Б';
                    if(flag) nc = c;
                    break;
                case '.':  nc = 'Ю';
                    if(flag) nc = c;
                    break;
                case 'І':  nc = 'Ы';
                    break; 
                case 'Ї':  nc = 'Ъ';
                    break;  
                case 'Є':  nc = 'Э';
                    break; 

                default:
                     nc = c;
                     break;
            }
            }
            
            if(mode==2){
                // -> Укр
            switch(c) {
                case 'q':  nc = 'й';
                    break;
                case 'w':  nc = 'ц';
                    break;
                case 'e':  nc = 'у';
                    break;
                case 'r':  nc = 'к';
                    break;
                case 't':  nc = 'е';
                    break;

                case 'y':  nc = 'н';
                    break;
                case 'u':  nc = 'г';
                    break;
                case 'i':  nc = 'ш';
                    break;
                case 'o':  nc = 'щ';
                    break;
                case 'p':  nc = 'з';
                    break;

                case '[':  nc = 'х';
                    break;
                case ']':  nc = 'ї';
                    break;
                case 'a':  nc = 'ф';
                    break;
                case 's':  nc = 'і';
                    break;
                case 'd':  nc = 'в';
                    break;

                case 'f':  nc = 'а';
                    break;
                case 'g':  nc = 'п';
                    break;
                case 'h':  nc = 'р';
                    break;
                case 'j':  nc = 'о';
                    break;
                case 'k':  nc = 'л';
                    break;

                case 'l':  nc = 'д';
                    break;
                case ';':  nc = 'ж';
                    break;
                case "'":  nc = 'є';
                    break;
                case 'z':  nc = 'я';
                    break;
                case 'x':  nc = 'ч';
                    break;

                case 'c':  nc = 'с';
                    break;
                case 'v':  nc = 'м';
                    break;
                case "b":  nc = 'и';
                    break;
                case 'n':  nc = 'т';
                    break;
                case 'm':  nc = 'ь';
                    break;
                case ',':  nc = 'б';
                    if(flag) nc = c;
                    break;
                case '.':  nc = 'ю';
                    if(flag) nc = c;
                    break;
                case 'ы':  nc = 'і';
                    break; 
                case 'ъ':  nc = 'ї';
                    break;  
                case 'э':  nc = 'є';
                    break; 


                case 'Q':  nc = 'Й';
                    break;
                case 'W':  nc = 'Ц';
                    break;
                case 'E':  nc = 'У';
                    break;
                case 'R':  nc = 'К';
                    break;
                case 'T':  nc = 'Е';
                    break;

                case 'Y':  nc = 'Н';
                    break;
                case 'U':  nc = 'Г';
                    break;
                case 'I':  nc = 'Ш';
                    break;
                case 'O':  nc = 'Щ';
                    break;
                case 'P':  nc = 'З';
                    break;

                case '[':  nc = 'Х';
                    break;
                case ']':  nc = 'Ї';
                    break;
                case 'A':  nc = 'Ф';
                    break;
                case 'S':  nc = 'І';
                    break;
                case 'D':  nc = 'В';
                    break;

                case 'F':  nc = 'А';
                    break;
                case 'G':  nc = 'П';
                    break;
                case 'H':  nc = 'Р';
                    break;
                case 'J':  nc = 'О';
                    break;
                case 'K':  nc = 'Л';
                    break;

                case 'L':  nc = 'Д';
                    break;
                case ';':  nc = 'Ж';
                    break;
                case "'":  nc = 'Є';
                    break;
                case 'Z':  nc = 'Я';
                    break;
                case 'X':  nc = 'Ч';
                    break;

                case 'C':  nc = 'С';
                    break;
                case 'V':  nc = 'М';
                    break;
                case "B":  nc = 'И';
                    break;
                case 'N':  nc = 'Т';
                    break;
                case 'M':  nc = 'Ь';
                    break;
                case ',':  nc = 'Б';
                    if(flag) nc = c;
                    break;
                case '.':  nc = 'Ю';
                    if(flag) nc = c;
                    break;
                case 'Ы':  nc = 'І';
                    break; 
                case 'Ъ':  nc = 'Ї';
                    break;  
                case 'Э':  nc = 'Є';
                    break; 


                default:
                     nc = c;
                     break;
            }
            }
            
            if(mode==3){
                // -> Лат
            switch(c) {
                case 'й':  nc = 'q';
                    break;
                case 'ц':  nc = 'w';
                    break;
                case 'у':  nc = 'e';
                    break;
                case 'к':  nc = 'r';
                    break;
                case 'е':  nc = 't';
                    break;

                case 'н':  nc = 'y';
                    break;
                case 'г':  nc = 'u';
                    break;
                case 'ш':  nc = 'i';
                    break;
                case 'щ':  nc = 'o';
                    break;
                case 'з':  nc = 'p';
                    break;

                case 'х':  nc = '[';
                    break;
                case 'ъ':  nc = ']';
                    break;
                case 'ф':  nc = 'a';
                    break;
                case 'ы':  nc = 's';
                    break;
                case 'в':  nc = 'd';
                    break;

                case 'а':  nc = 'f';
                    break;
                case 'п':  nc = 'g';
                    break;
                case 'р':  nc = 'h';
                    break;
                case 'о':  nc = 'j';
                    break;
                case 'л':  nc = 'k';
                    break;

                case 'д':  nc = 'l';
                    break;
                case 'ж':  nc = ';';
                    break;
                case "э":  nc = "'";
                    break;
                case 'я':  nc = 'z';
                    break;
                case 'ч':  nc = 'x';
                    break;

                case 'с':  nc = 'c';
                    break;
                case 'м':  nc = 'v';
                    break;
                case "и":  nc = 'b';
                    break;
                case 'т':  nc = 'n';
                    break;
                case 'ь':  nc = 'm';
                    break;
                case 'б':  nc = ',';
                    break;
                case 'ю':  nc = '.';
                    break;
                case 'і':  nc = 's';
                    break;
                case 'ї':  nc = ']';
                    break;
                case 'є':  nc = "'";
                    break;

                
                case 'Й':  nc = 'Q';
                    break;
                case 'Ц':  nc = 'W';
                    break;
                case 'У':  nc = 'E';
                    break;
                case 'К':  nc = 'R';
                    break;
                case 'Е':  nc = 'T';
                    break;

                case 'Н':  nc = 'Y';
                    break;
                case 'Г':  nc = 'U';
                    break;
                case 'Ш':  nc = 'I';
                    break;
                case 'Щ':  nc = 'O';
                    break;
                case 'З':  nc = 'P';
                    break;

                case 'Х':  nc = '[';
                    break;
                case 'Ъ':  nc = ']';
                    break;
                case 'Ф':  nc = 'A';
                    break;
                case 'Ы':  nc = 'S';
                    break;
                case 'В':  nc = 'D';
                    break;

                case 'А':  nc = 'F';
                    break;
                case 'П':  nc = 'G';
                    break;
                case 'Р':  nc = 'H';
                    break;
                case 'О':  nc = 'J';
                    break;
                case 'Л':  nc = 'K';
                    break;

                case 'Д':  nc = 'L';
                    break;
                case 'Ж':  nc = ';';
                    break;
                case "Э":  nc = "'";
                    break;
                case 'Я':  nc = 'Z';
                    break;
                case 'Ч':  nc = 'X';
                    break;

                case 'С':  nc = 'C';
                    break;
                case 'М':  nc = 'V';
                    break;
                case "И":  nc = 'B';
                    break;
                case 'Т':  nc = 'N';
                    break;
                case 'Ь':  nc = 'M';
                    break;
                case 'Б':  nc = ',';
                    break;
                case 'Ю':  nc = '.';
                    break;
                case 'І':  nc = 'S';
                    break;
                case 'Ї':  nc = ']';
                    break;
                case 'Є':  nc = "'";
                    break;

                default:
                     nc = c;
                     break;
            }
            }
            phrase = phrase + nc;
        }

        //alert(this.val);
        var exec = "$('"+elem+"')"+".val(phrase)";
        var focus = "$('"+elem+"')"+".focus()";
       
        eval(exec);
        eval(focus);
        //return phrase;
    }

// Создание меню для перекодирования символов строки
function rmenu(p,elem){
    var menu,div;
        div = '#rmenu-'+elem.substr(1);
        
        y = p.length;
        if(y==0) return false;
        menu = '<ul id="recode-menu">'+
        '<li><a class="rcd_ru" href="#" onclick='+'"'+"recode_txt("+"'"+p+"'"+','+"'"+elem+"'"+",2);"+" return false;"+'"'+'>Укр.</a></li>'+
        '<li><a class="rcd_ua" href="#" onclick='+'"'+"recode_txt("+"'"+p+"'"+','+"'"+elem+"'"+",1);"+" return false;"+'"'+'>Рос.</a></li>'+
        '<li><a class="rcd_en" href="#" onclick='+'"'+"recode_txt("+"'"+p+"'"+','+"'"+elem+"'"+",3);"+" return false;"+'"'+'>Лат.</a></li>'+
        '<li><a class="rcd_en" href="#" onclick='+'"'+"recode_txt("+"'"+p+"'"+','+"'"+elem+"'"+",4);"+" return false;"+'"'+'>Всі великі</a></li>'+
        '<li><a class="rcd_en" href="#" onclick='+'"'+"recode_txt("+"'"+p+"'"+','+"'"+elem+"'"+",5);"+" return false;"+'"'+'>Всі малі</a></li>'+
        '</ul>';
      
//       var div = document.createElement('div');
//	div.id = 'rmenu';
        
//        $("#rmenu").html(menu);
//        $("#rmenu").show();
         $(div).html(menu);
         $(div).show();
                  
    }   
    
window.onload=function(){
    $(document).click(function(e){

	  if ($(e.target).closest("#recode-menu").length) return;

	   $(".rmenu").hide();

	  e.stopPropagation();

	  });
   }        
