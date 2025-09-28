<?php 
$page_title = "Student Transfer";
include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_header.php'; 

?>

                    <label for="new-school">New School Name</label>
                    <input type="text" id="new-school" name="new_school" required>
                </div>
                 <div class="form-group">
                    <label for="new-school-udise">New School UDISE Code (if known)</label>
                    <input type="text" id="new-school-udise" name="new_school_udise">
                </div>
            </div>

             <div class="form-group">
                <label for="reason">Reason for Transfer</label>
                <textarea id="reason" name="reason" rows="4" required placeholder="e.g., Guardian's job transfer, family relocation..."></textarea>
            </div>

            <div class="form-group button-group-right">
                <button type="submit" class="btn-submit">Submit for Approval</button>
            </div>
        </form>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_footer.php'; ?>

