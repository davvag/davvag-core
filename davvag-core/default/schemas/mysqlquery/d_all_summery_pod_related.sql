ALTER TABLE d_all_summery 
ADD FULLTEXT INDEX d_all_pod_related_idx (summery ASC, title ASC, keywords ASC);
