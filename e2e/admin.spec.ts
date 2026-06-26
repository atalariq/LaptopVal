import { test, expect } from "@playwright/test";

const ADMIN_USER = process.env.ADMIN_USERNAME ?? "admin";
const ADMIN_PASS = process.env.ADMIN_PASSWORD ?? "admin123";

test.describe("admin auth", () => {
  test("redirects unauthenticated access to login", async ({ page }) => {
    await page.goto("/admin");
    await expect(page).toHaveURL(/\/login/);
    await expect(
      page.getByRole("heading", { name: "Login Admin" }),
    ).toBeVisible();
  });

  test("rejects bad credentials", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("Username").fill(ADMIN_USER);
    await page.getByLabel("Password").fill("wrong-password");
    await page.getByRole("button", { name: "Masuk" }).click();
    await expect(page.getByText("Username atau password salah.")).toBeVisible();
  });

  test("logs in, reaches dashboard, and logs out", async ({ page }) => {
    await page.goto("/login");
    await page.getByLabel("Username").fill(ADMIN_USER);
    await page.getByLabel("Password").fill(ADMIN_PASS);
    await page.getByRole("button", { name: "Masuk" }).click();

    await expect(page).toHaveURL(/\/admin$/);
    await expect(page.getByRole("link", { name: "Dashboard" })).toBeVisible();

    await page.getByRole("button", { name: "Logout" }).click();
    // Logout clears the session; admin is no longer reachable.
    await page.goto("/admin");
    await expect(page).toHaveURL(/\/login/);
  });
});
