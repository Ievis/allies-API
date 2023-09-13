(select * from `telegram_dating_users`
where `id` != $id
and `subject` = $subject
and `category` = $category
and `id` not in ($excluded_ids)
and exists (select * from `telegram_dating_feedback`
where `telegram_dating_users`.`id` = `telegram_dating_feedback`.`first_user_id`
and `first_user_reaction` = $first_user_reaction
and `second_user_id` = $second_user_id
and `subject` = $subject
and `category` = $category
and `is_resolved` = $is_resolved)
or `id` in ($included_ids)
and `id` not in ($excluded_ids))
union
(select * from `telegram_dating_users`
where `id` != $id
and `subject` = $subject
and `category` = $category
and `id` not in ($excluded_ids)
and not exists (select * from `telegram_dating_feedback`
where `telegram_dating_users`.`id` = `telegram_dating_feedback`.`first_user_id`
and `second_user_id` = $second_user_id
and `subject` = $subject
and `category` = $category)
and not exists (select * from `telegram_dating_feedback`
where `telegram_dating_users`.`id` = `telegram_dating_feedback`.`second_user_id`
and `first_user_id` = $first_user_id
and `subject` = $subject
and `category` = $category))
limit 5