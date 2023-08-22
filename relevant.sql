(select * from `telegram_dating_users`
where `id` != $id
and `subject` = $subject
and `category` = $category
and exists (select * from `telegram_dating_feedback`
where `telegram_dating_users`.`id` = `telegram_dating_feedback`.`first_user_id`
and `first_user_reaction` = true
and `second_user_id` = $id
and `subject` = $subject
and `category` = $category
and `is_resolved` = false))
union (select * from `telegram_dating_users`
where `id` != $id and `subject` = $subject
and `category` = $category
and not exists (select * from `telegram_dating_feedback`
where `telegram_dating_users`.`id` = `telegram_dating_feedback`.`first_user_id`
and `second_user_id` = $id
and `subject` = $subject
and `category` = $category)
and not exists (select * from `telegram_dating_feedback`
where `telegram_dating_users`.`id` = `telegram_dating_feedback`.`second_user_id`
and `first_user_id` = $id
and `subject` = $subject
and `category` = $category))
limit 5