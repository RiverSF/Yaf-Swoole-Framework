### 2018-07-31  Author: River
> 修改表结构，在 `payable` 表添加新增两个字段：离店日期 - `leave_time`, 不可取消日 - `non_cancel_time`
```mysql
ALTER TABLE `payable` ADD COLUMN `leave_time` date COMMENT '离店日期' AFTER `use_time`,
 ADD COLUMN `non_cancel_time` date COMMENT '不可取消日' AFTER `leave_time`;
```