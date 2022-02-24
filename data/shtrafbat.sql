select concat(substr(data_p,9,2),'.',substr(data_p,6,2),'.',substr(data_p,1,4)) as data_p,tel,fio,main_unit,case when days<63 then 0 else shtraf end as shtraf,
case when days<63 then 1 else 0 end as attention from (
SELECT DATEDIFF(now(),d.data_p) as days,now(),d.data_p,a.tel,a.fio,c.main_unit,(cast(REPLACE(b.cost_all, ',', '.') as dec(6,2))-cast(REPLACE(b.cost_plan, ',', '.') as dec(6,2))-10) as shtraf
FROM `kyivstar` a left join tmp_ks0520 b ON trim(a.tel)=trim(b.tel) 
LEFT JOIN vw_phone c on trim(a.fio) = trim(c.fio)
left join data d on a.tab_nom=cast(d.tab_nom as unsigned)
where (cast(REPLACE(b.cost_all, ',', '.') as dec(6,2))-10)-(cast(REPLACE(b.cost_plan, ',', '.') as dec(6,2)))>0 order by shtraf desc
) r    